<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\University;
use App\Models\EntryCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    // Cache duration in minutes (1 hour to match Flutter cache)
    private const CACHE_DURATION = 60;

    /**
     * Get all courses
     * Endpoint: GET /courses
     */
    public function getCourses(Request $request): JsonResponse
    {
        $semesterId = $request->query('semester_id');
        $universityId = $request->query('university_id');
        
        $cacheKey = "courses_semester_{$semesterId}_university_{$universityId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($semesterId, $universityId) {
            $query = Course::where('status', 'active');
            
            if ($semesterId) {
                $query->whereHas('semesters', function ($q) use ($semesterId) {
                    $q->where('semester_id', $semesterId);
                });
            }
            
            if ($universityId) {
                $query->where('university_id', $universityId);
            }
            
            $courses = $query->with(['university:id,name', 'department:id,name'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $courses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'name' => $course->name,
                        'code' => $course->code,
                        'description' => $course->description,
                        'duration' => $course->duration,
                        'degree_type' => $course->degree_type,
                        'university_id' => $course->university_id,
                        'university_name' => $course->university->name ?? null,
                        'department_name' => $course->department->name ?? null,
                        'total_semesters' => $course->total_semesters,
                        'created_at' => $course->created_at,
                    ];
                })->toArray()
            ]);
        });
    }

    /**
     * Get entry categories (for item classification)
     * Endpoint: GET /entry-categories
     */
    public function getEntryCategories(): JsonResponse
    {
        return Cache::remember('entry_categories', self::CACHE_DURATION, function () {
            $entryCategories = EntryCategory::where('status', 'active')
                ->orderBy('name')
                ->get();

            // If no categories in database, return default categories
            if ($entryCategories->isEmpty()) {
                $defaultCategories = $this->getDefaultCategories();
                
                return response()->json([
                    'success' => true,
                    'data' => $defaultCategories
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $entryCategories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'description' => $category->description,
                        'icon' => $category->icon,
                        'color' => $category->color,
                        'created_at' => $category->created_at,
                    ];
                })->toArray()
            ]);
        });
    }

    /**
     * Get all semesters
     * Endpoint: GET /semesters
     */
    public function getSemesters(Request $request): JsonResponse
    {
        $courseId = $request->query('course_id');
        
        $cacheKey = "semesters_course_{$courseId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($courseId) {
            $query = Semester::where('status', 'active');
            
            if ($courseId) {
                $query->whereHas('courses', function ($q) use ($courseId) {
                    $q->where('course_id', $courseId);
                });
            }
            
            $semesters = $query->orderBy('sequence')->get();

            // If no semesters in database, return default semesters
            if ($semesters->isEmpty()) {
                $defaultSemesters = $this->generateDefaultSemesters();
                
                return response()->json([
                    'success' => true,
                    'data' => $defaultSemesters
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $semesters->map(function ($semester) {
                    return [
                        'id' => $semester->id,
                        'name' => $semester->name,
                        'sequence' => $semester->sequence,
                        'duration' => $semester->duration ?? '6 months',
                        'start_month' => $semester->start_month,
                        'end_month' => $semester->end_month,
                        'academic_year' => $semester->academic_year,
                        'created_at' => $semester->created_at,
                    ];
                })->toArray()
            ]);
        });
    }

    /**
     * Get all subjects
     * Endpoint: GET /subjects
     */
    public function getSubjects(Request $request): JsonResponse
    {
        $courseId = $request->query('course_id');
        $semesterId = $request->query('semester_id');
        $universityId = $request->query('university_id');
        
        $cacheKey = "subjects_course_{$courseId}_semester_{$semesterId}_university_{$universityId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($courseId, $semesterId, $universityId) {
            $query = Subject::where('status', 'active');
            
            if ($courseId) {
                $query->where('course_id', $courseId);
            }
            
            if ($semesterId) {
                $query->where('semester_id', $semesterId);
            }
            
            if ($universityId) {
                $query->where('university_id', $universityId);
            }
            
            $subjects = $query->with(['course:id,name', 'semester:id,name'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subjects->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'code' => $subject->code,
                        'description' => $subject->description,
                        'course_id' => $subject->course_id,
                        'course_name' => $subject->course->name ?? null,
                        'semester_id' => $subject->semester_id,
                        'semester_name' => $subject->semester->name ?? null,
                        'university_id' => $subject->university_id,
                        'credits' => $subject->credits,
                        'created_at' => $subject->created_at,
                    ];
                })->toArray()
            ]);
        });
    }

    /**
     * Get all universities
     * Endpoint: GET /universities
     */
    public function getUniversities(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $countryId = $request->query('country_id');
        $state = $request->query('state');
        
        $cacheKey = "universities_search_" . md5($search . $countryId . $state);
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($search, $countryId, $state) {
            $query = University::where('status', 'active');
            
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('code', 'LIKE', "%{$search}%")
                      ->orWhere('city', 'LIKE', "%{$search}%");
                });
            }
            
            if ($countryId) {
                $query->where('country_id', $countryId);
            }
            
            if ($state) {
                $query->where('state', 'LIKE', "%{$state}%");
            }
            
            $universities = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $universities->map(function ($university) {
                    return [
                        'id' => $university->id,
                        'name' => $university->name,
                        'code' => $university->code,
                        'description' => $university->description,
                        'city' => $university->city,
                        'state' => $university->state,
                        'country_id' => $university->country_id,
                        'website' => $university->website,
                        'logo' => $university->logo,
                        'type' => $university->type,
                        'established_year' => $university->established_year,
                        'ranking' => $university->ranking,
                        'created_at' => $university->created_at,
                    ];
                })->toArray()
            ]);
        });
    }

    /**
     * Clear cache for specific key or all cache
     * Endpoint: POST /clear-cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        $key = $request->input('key');
        
        if ($key) {
            // Clear specific cache
            Cache::forget($key);
            $message = "Cache cleared for key: {$key}";
        } else {
            // Clear all category-related cache
            $cacheKeys = [
                'courses*',
                'entry_categories',
                'semesters*',
                'subjects*',
                'universities*',
            ];

            // Since we can't use wildcards directly, we flush all cache
            Cache::flush();
            $message = "All cache cleared successfully";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Default categories (fallback data)
     */
    private function getDefaultCategories(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Engineering',
                'description' => 'Engineering courses',
                'icon' => '🔧',
                'color' => '#FF6B6B',
                'created_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Medical',
                'description' => 'Medical courses',
                'icon' => '🏥',
                'color' => '#4ECDC4',
                'created_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'Arts',
                'description' => 'Arts courses',
                'icon' => '🎨',
                'color' => '#45B7D1',
                'created_at' => now()
            ],
            [
                'id' => 4,
                'name' => 'Commerce',
                'description' => 'Commerce courses',
                'icon' => '💼',
                'color' => '#F39C12',
                'created_at' => now()
            ],
            [
                'id' => 5,
                'name' => 'Science',
                'description' => 'Science courses',
                'icon' => '🔬',
                'color' => '#9B59B6',
                'created_at' => now()
            ],
            [
                'id' => 6,
                'name' => 'Law',
                'description' => 'Law courses',
                'icon' => '⚖️',
                'color' => '#34495E',
                'created_at' => now()
            ],
        ];
    }

    /**
     * Default semesters (fallback data)
     */
    private function generateDefaultSemesters(): array
    {
        $semesters = [];
        for ($i = 1; $i <= 8; $i++) {
            $semesters[] = [
                'id' => $i,
                'name' => "Semester {$i}",
                'sequence' => $i,
                'duration' => '6 months',
                'start_month' => ($i % 2 === 1) ? 'July' : 'January',
                'end_month' => ($i % 2 === 1) ? 'December' : 'June',
                'academic_year' => ceil($i / 2),
                'created_at' => now(),
            ];
        }
        return $semesters;
    }
}
