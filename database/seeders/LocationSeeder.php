<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $countryIdIndia = 1; // India in countries table

        // --- Cities ---
        $cities = [
            ['name' => 'Shimla', 'state' => 'Himachal Pradesh', 'latitude' => 31.1048, 'longitude' => 77.1734],
            ['name' => 'Chaupal', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Jubbal', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Kotkhai', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Kumarsain', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Narkanda', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Rampur', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Rohru', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Theog', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Kufri', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Mashobra', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Chail', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Annandale', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Jakhoo Hill', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Jutogh', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Summer Hill', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Sanjauli', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Naldehra', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Baddi', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Nurpur', 'state' => 'Himachal Pradesh', 'latitude' => null, 'longitude' => null],
            ['name' => 'Kalka', 'state' => 'Himachal Pradesh', 'latitude' => 30.8414, 'longitude' => 76.9511],
            ['name' => 'Dharamshala', 'state' => 'Himachal Pradesh', 'latitude' => 32.2190, 'longitude' => 76.3234],
            ['name' => 'Palampur', 'state' => 'Himachal Pradesh', 'latitude' => 32.1050, 'longitude' => 76.5360],
            ['name' => 'Mandi', 'state' => 'Himachal Pradesh', 'latitude' => 31.7089, 'longitude' => 76.9316],
            ['name' => 'Solan', 'state' => 'Himachal Pradesh', 'latitude' => 30.9083, 'longitude' => 77.0966],
            ['name' => 'Kangra', 'state' => 'Himachal Pradesh', 'latitude' => 32.1050, 'longitude' => 76.5360],
            ['name' => 'Hamirpur', 'state' => 'Himachal Pradesh', 'latitude' => 31.6896, 'longitude' => 76.5262],
            ['name' => 'Bilaspur', 'state' => 'Himachal Pradesh', 'latitude' => 31.5446, 'longitude' => 76.9145],
            ['name' => 'Manali', 'state' => 'Himachal Pradesh', 'latitude' => 32.2396, 'longitude' => 77.1887],
            ['name' => 'Una', 'state' => 'Himachal Pradesh', 'latitude' => 31.4673, 'longitude' => 76.2713],
            ['name' => 'Chamba', 'state' => 'Himachal Pradesh', 'latitude' => 32.5529, 'longitude' => 76.0735],
            ['name' => 'Keylong', 'state' => 'Himachal Pradesh', 'latitude' => 32.9631, 'longitude' => 77.0891],
            ['name' => 'Nahan', 'state' => 'Himachal Pradesh', 'latitude' => 30.5598, 'longitude' => 77.2675],
            ['name' => 'Ghumarwin', 'state' => 'Himachal Pradesh', 'latitude' => 31.5150, 'longitude' => 76.6666],
            ['name' => 'Mumbai', 'state' => 'Maharashtra', 'latitude' => 19.0760, 'longitude' => 72.8777],
            ['name' => 'Delhi', 'state' => 'Delhi', 'latitude' => 28.7041, 'longitude' => 77.1025],
            ['name' => 'Bengaluru', 'state' => 'Karnataka', 'latitude' => 12.9716, 'longitude' => 77.5946],
            ['name' => 'Kolkata', 'state' => 'West Bengal', 'latitude' => 22.5726, 'longitude' => 88.3639],
            ['name' => 'Chennai', 'state' => 'Tamil Nadu', 'latitude' => 13.0827, 'longitude' => 80.2707],
            ['name' => 'Hyderabad', 'state' => 'Telangana', 'latitude' => 17.3850, 'longitude' => 78.4867],
            ['name' => 'Ahmedabad', 'state' => 'Gujarat', 'latitude' => 23.0225, 'longitude' => 72.5714],
            ['name' => 'Pune', 'state' => 'Maharashtra', 'latitude' => 18.5204, 'longitude' => 73.8567],
            ['name' => 'Surat', 'state' => 'Gujarat', 'latitude' => 21.1702, 'longitude' => 72.8311],
            ['name' => 'Jaipur', 'state' => 'Rajasthan', 'latitude' => 26.9124, 'longitude' => 75.7873],
            ['name' => 'Lucknow', 'state' => 'Uttar Pradesh', 'latitude' => 26.8467, 'longitude' => 80.9462],
            ['name' => 'Kanpur', 'state' => 'Uttar Pradesh', 'latitude' => 26.4499, 'longitude' => 80.3319],
            ['name' => 'Nagpur', 'state' => 'Maharashtra', 'latitude' => 21.1458, 'longitude' => 79.0882],
            ['name' => 'Indore', 'state' => 'Madhya Pradesh', 'latitude' => 22.7196, 'longitude' => 75.8577],
            ['name' => 'Bhopal', 'state' => 'Madhya Pradesh', 'latitude' => 23.2599, 'longitude' => 77.4126],
            ['name' => 'Patna', 'state' => 'Bihar', 'latitude' => 25.5941, 'longitude' => 85.1376],
            ['name' => 'Visakhapatnam', 'state' => 'Andhra Pradesh', 'latitude' => 17.6868, 'longitude' => 83.2185],
            ['name' => 'Vadodara', 'state' => 'Gujarat', 'latitude' => 22.3072, 'longitude' => 73.1812],
            ['name' => 'Agra', 'state' => 'Uttar Pradesh', 'latitude' => 27.1767, 'longitude' => 78.0081],
            ['name' => 'Amritsar', 'state' => 'Punjab', 'latitude' => 31.6340, 'longitude' => 74.8723],
        ];

        $cityIds = [];
        foreach ($cities as $city) {
            $cityId = DB::table('cities')->insertGetId([
                'name' => $city['name'],
                'state' => $city['state'],
                'country_code' => 'IN',
                'latitude' => $city['latitude'],
                'longitude' => $city['longitude'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now
            ]);
            $cityIds[$city['name']] = $cityId; // FIXED: Store just the ID, not an array
        }

        // --- Universities ---
        $universities = [
            ['name' => 'Himachal Pradesh University (HPU), Shimla', 'city' => 'Shimla', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.hpuniv.ac.in', 'logo' => 'https://hpuniv.ac.in/front/assets/images/hpu-logo.png', 'established_year' => 1970, 'ranking' => null],
            ['name' => 'Centre of Excellence Government College Sanjauli', 'city' => 'Shimla', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcsanjauli.com', 'logo' => 'https://www.gcsanjauli.edu.in/images/logo.gif', 'established_year' => null, 'ranking' => null],
            ['name' => 'Rajiv Gandhi Government College', 'city' => 'Chaura Maidan, Shimla', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gccm.ac.in', 'logo' => 'https://www.gccm.ac.in/images/college_61248.png', 'established_year' => 1984, 'ranking' => null],
            ['name' => 'University Institute of Information Technology (UIIT), HPU', 'city' => 'Shimla', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.uiit.ac.in', 'logo' => null, 'established_year' => 2000, 'ranking' => null],
            ['name' => 'Himachal Institute of Technology, Paonta Sahib (near Shimla)', 'city' => 'Shimla', 'type' => 'private', 'state' => null, 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Apeejay Stya University, Sohna (near Shimla)', 'city' => 'Shimla', 'type' => 'private', 'state' => null, 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Himachal Pradesh National Law University (HPNLU)', 'city' => 'Shimla', 'type' => 'autonomous', 'state' => 'Himachal Pradesh', 'website' => 'www.hpnlu.ac.in', 'logo' => 'https://hpnlu.ac.in/wp-content/uploads/2016/06/cropped-logonlu1.png', 'established_year' => 2016, 'ranking' => null],
            ['name' => 'A.P.G. Shimla University', 'city' => 'Shimla', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.agu.edu.in', 'logo' => 'https://www.agu.edu.in/images/logo.png', 'established_year' => 2012, 'ranking' => null],
            ['name' => 'Indira Gandhi Medical College (IGMC)', 'city' => 'Shimla', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.igmcshimla.edu.in', 'logo' => 'https://www.igmcshimla.edu.in/wp-content/themes/igmc/images/logo.png', 'established_year' => 1966, 'ranking' => null],
            ['name' => 'Rajkiya Kanya Mahavidyalaya (RKMV)', 'city' => 'Shimla', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.rkmvshimla.edu.in', 'logo' => 'https://rkmvshimla.edu.in/img/logo.jpg', 'established_year' => 1977, 'ranking' => null],
            ['name' => "St. Bede's College", 'city' => 'Shimla', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.stbedescollege.in', 'logo' => 'https://stbedescollege.in/assets/images/logo.png', 'established_year' => 1904, 'ranking' => null],

            ['name' => 'Dr. Y.S. Parmar University of Horticulture and Forestry', 'city' => 'Solan', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.yspuniversity.ac.in', 'logo' => 'https://www.yspuniversity.ac.in/images/logo.png', 'established_year' => 1985, 'ranking' => null],
            ['name' => 'Jaypee University of Information Technology (JUIT)', 'city' => 'Solan', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.juit.ac.in', 'logo' => 'https://www.juit.ac.in/images/juit_logo.png', 'established_year' => 2002, 'ranking' => null],
            ['name' => 'Shoolini University of Biotechnology and Management Sciences', 'city' => 'Solan', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.shooliniuniversity.com', 'logo' => 'https://shooliniuniversity.com/wp-content/uploads/2023/10/logo-dark-1.png', 'established_year' => 2009, 'ranking' => null],
            ['name' => 'Manav Bharti University', 'city' => 'Solan', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.manavbhartiuniversity.edu.in', 'logo' => 'https://www.manavbhartiuniversity.edu.in/images/mbu.png', 'established_year' => 2009, 'ranking' => null],
            ['name' => 'Maharishi Markandeshwar University', 'city' => 'Solan', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.mmusolan.org', 'logo' => 'https://mmusolan.org/assets/images/logo.png', 'established_year' => 2010, 'ranking' => null],
            ['name' => 'IEC University', 'city' => 'Solan', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.iecuniversity.com', 'logo' => 'https://iecuniversity.com/front-assets/images/logo.png', 'established_year' => 2012, 'ranking' => null],
            ['name' => 'Maharaja Agrasen University', 'city' => 'Solan', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.mau.ac.in', 'logo' => 'https://www.mau.ac.in/assets/images/mau-logo.png', 'established_year' => 2013, 'ranking' => null],
            ['name' => 'Chitkara University, Barotiwala', 'city' => 'Solan', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.chitkara.edu.in', 'logo' => 'https://www.chitkara.edu.in/images/new-logo.png', 'established_year' => 2008, 'ranking' => null],
            ['name' => 'ICFAI University, Baddi', 'city' => 'Solan', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.iuhimachal.edu.in', 'logo' => 'https://www.iuhimachal.edu.in/assets/img/logo.jpg', 'established_year' => 2011, 'ranking' => null],
            ['name' => 'Green Hills Engineering College', 'city' => 'Solan', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.ghec.co.in', 'logo' => 'https://www.ghec.co.in/images/logo.png', 'established_year' => 2003, 'ranking' => null],
            ['name' => 'LR Institute of Engineering and Technology', 'city' => 'Solan', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.lrinstitutes.com', 'logo' => 'https://www.lrinstitutes.com/images/lri-logo.png', 'established_year' => 2006, 'ranking' => null],
            ['name' => 'Government Post Graduate College, Solan', 'city' => 'Solan', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcsolan.ac.in', 'logo' => null, 'established_year' => 1959, 'ranking' => null],
            ['name' => 'Baddi University of Emerging Sciences and Technologies', 'city' => 'Solan', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.baddiuniv.ac.in', 'logo' => 'https://www.baddiuniv.ac.in/images/buest.png', 'established_year' => 2009, 'ranking' => null],
            ['name' => 'Bahra University, Waknaghat', 'city' => 'Solan', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.bahrauniversity.edu.in', 'logo' => '[suspicious link removed]', 'established_year' => 2011, 'ranking' => null],
            ['name' => 'Government Degree College, Arki', 'city' => 'Solan', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcarki.ac.in', 'logo' => null, 'established_year' => 1999, 'ranking' => null],
            ['name' => 'Government College, Nalagarh', 'city' => 'Solan', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdcnalagarh.com', 'logo' => null, 'established_year' => 1974, 'ranking' => null],
            ['name' => 'Government Degree College, Dharmpur', 'city' => 'Solan', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdcdharmpur.in', 'logo' => null, 'established_year' => 2016, 'ranking' => null],

            ['name' => 'K.C. Institute of Engineering and Technology', 'city' => 'Una', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.kcinstitutes.com', 'logo' => 'https://www.kcinstitutes.com/kc.png', 'established_year' => 2009, 'ranking' => null],
            ['name' => 'Indian Institute of Information Technology (IIIT)', 'city' => 'Una', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.iiituna.ac.in', 'logo' => 'https://iiituna.ac.in/wp-content/themes/IIITU/images/logo.png', 'established_year' => 2014, 'ranking' => null],
            ['name' => 'Indus International University', 'city' => 'Una', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.iiuedu.org', 'logo' => 'https://www.iiuedu.org/uploads/logo/1699504547_logo.png', 'established_year' => 2009, 'ranking' => null],
            ['name' => 'Government Post Graduate College, Una', 'city' => 'Una', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.govtpgcollegeuna.in', 'logo' => null, 'established_year' => 1968, 'ranking' => null],
            ['name' => 'Dr. B.R. Ambedkar Government Polytechnic, Ambota', 'city' => 'Una', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gpambota.edu.in', 'logo' => null, 'established_year' => 1995, 'ranking' => null],
            ['name' => 'Maharana Pratap Government College, Amb', 'city' => 'Una', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.mpgcamb.com', 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Government Degree College, Haroli', 'city' => 'Una', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'https://gdcharoli.co.in/', 'logo' => null, 'established_year' => 2017, 'ranking' => null],
            ['name' => 'Government Degree College, Gagret', 'city' => 'Una', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Government Degree College, Daulatpur Chowk', 'city' => 'Una', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Government Degree College, Bangana', 'city' => 'Una', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],

            ['name' => 'All India Institute of Medical Sciences (AIIMS), Bilaspur', 'city' => 'Bilaspur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.aiimsbilaspur.edu.in', 'logo' => 'https://www.aiimsbilaspur.edu.in/Content/images/logo.png', 'established_year' => 2017, 'ranking' => null],
            ['name' => 'Government Post Graduate College, Bilaspur', 'city' => 'Bilaspur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcbilaspur.in', 'logo' => 'https://gcbilaspur.in/wp-content/themes/college/images/logo.png', 'established_year' => 1952, 'ranking' => null],
            ['name' => 'Shiva Institute of Engineering and Technology', 'city' => 'Bilaspur', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.shiva.edu.in', 'logo' => 'https://www.shiva.edu.in/assets/images/logo-dark.png', 'established_year' => 2007, 'ranking' => null],
            ['name' => 'Government Polytechnic College, Kalol', 'city' => 'Bilaspur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gpbilaspur.ac.in', 'logo' => null, 'established_year' => 1995, 'ranking' => null],
            ['name' => 'Government College, Ghumarwin', 'city' => 'Bilaspur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcghumarwin.ac.in', 'logo' => null, 'established_year' => 1985, 'ranking' => null],
            ['name' => 'Shiva College of Education, Ghumarwin', 'city' => 'Bilaspur', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.shivaeducation.com', 'logo' => null, 'established_year' => 2007, 'ranking' => null],
            ['name' => 'Shri Shakti College of Education, Shri Naina Devi', 'city' => 'Bilaspur', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.srinainadeviji.com', 'logo' => null, 'established_year' => 1931, 'ranking' => null],
            ['name' => 'Shiva Ayurvedic Medical College', 'city' => 'Bilaspur', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.shivaayurvediccollege.org', 'logo' => null, 'established_year' => 2005, 'ranking' => null],
            ['name' => 'Government Degree College, Jhandutta', 'city' => 'Bilaspur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdcjhandutta.ac.in', 'logo' => null, 'established_year' => 2006, 'ranking' => null],
            ['name' => 'Government Degree College, Nainadevi', 'city' => 'Bilaspur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdc-nainadevi.com', 'logo' => null, 'established_year' => 2017, 'ranking' => null],

            ['name' => 'Himachal Pradesh Technical University (HPTU)', 'city' => 'Hamirpur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.himtu.ac.in', 'logo' => 'https://himtu.ac.in/img/himtu_logo.png', 'established_year' => 2010, 'ranking' => null],
            ['name' => 'National Institute of Technology (NIT), Hamirpur', 'city' => 'Hamirpur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.nith.ac.in', 'logo' => 'https://nith.ac.in/assets/uploads/images/NIT_logo.png', 'established_year' => 1986, 'ranking' => null],
            ['name' => 'Career Point University', 'city' => 'Hamirpur', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.cpuh.in', 'logo' => 'https://www.cpuh.in/images/cpu-logo.png', 'established_year' => 2012, 'ranking' => null],
            ['name' => 'Neta Ji Subhash Chander Bose Memorial Government College', 'city' => 'Hamirpur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gchamirpur.org', 'logo' => null, 'established_year' => 1965, 'ranking' => null],
            ['name' => 'Dr. Radhakrishnan Government Medical College', 'city' => 'Hamirpur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.rgmch.ac.in', 'logo' => 'https://www.rgmch.ac.in/image/logo.png', 'established_year' => 2018, 'ranking' => null],
            ['name' => 'Government Degree College, Nadaun', 'city' => 'Hamirpur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'http://gcnadaun.ac.in/', 'logo' => null, 'established_year' => 1974, 'ranking' => null],
            ['name' => 'Government Polytechnic College, Hamirpur', 'city' => 'Hamirpur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gph.edu.in', 'logo' => null, 'established_year' => 1963, 'ranking' => null],
            ['name' => 'Baba Balak Nath Post Graduate College', 'city' => 'Hamirpur', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.bbncollege.in', 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Government Degree College, Bhoranj', 'city' => 'Hamirpur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdc-bhoranj.org', 'logo' => null, 'established_year' => 1986, 'ranking' => null],
            ['name' => 'Government Degree College, Sujanpur Tira', 'city' => 'Hamirpur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcsujanpur.ac.in', 'logo' => null, 'established_year' => 1994, 'ranking' => null],

            ['name' => 'Atal Medical and Research University', 'city' => 'Mandi', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.amruhp.ac.in', 'logo' => 'https://amruhp.ac.in/wp-content/uploads/2021/08/logo.png', 'established_year' => 2018, 'ranking' => null],
            ['name' => 'Sardar Patel University (SPU), Mandi', 'city' => 'Mandi', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.spuv.ac.in', 'logo' => 'https://spuv.ac.in/spu_logo.png', 'established_year' => 2022, 'ranking' => null],
            ['name' => 'Indian Institute of Technology (IIT), Mandi', 'city' => 'Mandi', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.iitmandi.ac.in', 'logo' => 'https://www.iitmandi.ac.in/image/new_logo.png', 'established_year' => 2009, 'ranking' => null],
            ['name' => 'Abhilashi University', 'city' => 'Mandi', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.abhilashiuniversity.com', 'logo' => 'https://www.abhilashiuniversity.com/assets/images/logo/logo.png', 'established_year' => 2015, 'ranking' => null],
            ['name' => 'Vallabh Government College', 'city' => 'Mandi', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.vgcmandi.ac.in', 'logo' => 'https://vgcmandi.ac.in/images/logo.png', 'established_year' => 1948, 'ranking' => null],
            ['name' => 'Shri Lal Bahadur Shastri Government Medical College and Hospital, Ner Chowk', 'city' => 'Mandi', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.slbsgmcm.in', 'logo' => null, 'established_year' => 2009, 'ranking' => null],
            ['name' => 'Jawaharlal Nehru Government Engineering College (JNGEC), Sundernagar', 'city' => 'Mandi', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.jngec.ac.in', 'logo' => 'https://www.jngec.ac.in/img/logo.png', 'established_year' => 2006, 'ranking' => null],
            ['name' => 'Maharaja Lakshman Sen Memorial College (MLSM), Sundernagar', 'city' => 'Mandi', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.mlsmcollege.ac.in', 'logo' => 'https://www.mlsmcollege.ac.in/images/mlsm-logo.png', 'established_year' => 1970, 'ranking' => null],
            ['name' => 'Government Polytechnic College, Sundernagar', 'city' => 'Mandi', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gpsundernagar.org', 'logo' => 'https://www.gpsundernagar.org/images/logo.png', 'established_year' => 1962, 'ranking' => null],
            ['name' => 'Government Degree College, Sarkaghat', 'city' => 'Mandi', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcsarkaghat.ac.in', 'logo' => null, 'established_year' => 1985, 'ranking' => null],
            ['name' => 'Government Degree College, Jogindernagar', 'city' => 'Mandi', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcjogindernagar.in', 'logo' => null, 'established_year' => 1986, 'ranking' => null],
            ['name' => 'Government Degree College, Karsog', 'city' => 'Mandi', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gckarsog.org', 'logo' => null, 'established_year' => 1995, 'ranking' => null],
            ['name' => 'T.R. Abhilashi Memorial Institute of Engineering and Technology', 'city' => 'Mandi', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.tramiet.in', 'logo' => 'https://www.tramiet.in/img/logo.png', 'established_year' => 2007, 'ranking' => null],
            ['name' => 'SIRDA Institute of Engineering and Emerging Technologies', 'city' => 'Mandi', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.sirda.in', 'logo' => 'https://www.sirda.in/images/logo.png', 'established_year' => 2008, 'ranking' => null],
            ['name' => 'Abhilashi College of Pharmacy', 'city' => 'Mandi', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.acop.in', 'logo' => 'https://www.acop.in/images/logo.png', 'established_year' => 2006, 'ranking' => null],
            ['name' => 'Himalayan Group of Professional Institutions', 'city' => 'Mandi', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.hgte.in', 'logo' => 'https://www.hgte.in/images/logo.png', 'established_year' => 2008, 'ranking' => null],
            ['name' => 'Government Degree College, Bassa', 'city' => 'Mandi', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcbassa.co.in', 'logo' => null, 'established_year' => 1998, 'ranking' => null],

            ['name' => 'Government College Kukumseri, Kukumseri', 'city' => 'Lahul & Spiti', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'https://sites.google.com/view/govtcollegekukumseri', 'logo' => null, 'established_year' => 1995, 'ranking' => null],
            ['name' => 'Government College Pangi, Killar', 'city' => 'Lahul & Spiti', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'null', 'logo' => null, 'established_year' => 2007, 'ranking' => null],
            ['name' => 'Industrial Training Institute, Jahalma, Udaipur', 'city' => 'Lahul & Spiti', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'null', 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Govt. Industrial Training Institute, Keylong', 'city' => 'Lahul & Spiti', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'null', 'logo' => null, 'established_year' => null, 'ranking' => null],

            ['name' => 'Himalayan Institute of Engineering and Technology', 'city' => 'Sirmour', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.hgpi.in', 'logo' => 'https://hgpi.in/images/logo.png', 'established_year' => 2009, 'ranking' => null],
            ['name' => 'Indian Institute of Management (IIM), Paonta Sahib', 'city' => 'Sirmour', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.iimsirmaur.ac.in', 'logo' => 'https://iimsirmaur.ac.in/sites/default/files/IIM%20Sirmaur%20Logo_1.png', 'established_year' => 2015, 'ranking' => null],
            ['name' => 'Eternal University, Baru Sahib', 'city' => 'Sirmour', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.eternaluniversity.edu.in', 'logo' => 'https://eternaluniversity.edu.in/front-assets/images/logo.png', 'established_year' => 2008, 'ranking' => null],
            ['name' => 'Dr. Y.S. Parmar Government Medical College, Nahan', 'city' => 'Sirmour', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gmcnhn.org', 'logo' => 'https://gmcnhn.org/wp-content/uploads/2021/08/logo.png', 'established_year' => 2016, 'ranking' => null],
            ['name' => 'Government Post Graduate College, Nahan', 'city' => 'Sirmour', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcnahan.ac.in', 'logo' => 'https://gcnahan.ac.in/wp-content/uploads/2021/04/college_logo.jpg', 'established_year' => 1962, 'ranking' => null],
            ['name' => 'Himalayan Group of Professional Institutions, Kala Amb', 'city' => 'Sirmour', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.hgpi.in', 'logo' => 'https://hgpi.in/images/logo.png', 'established_year' => 2002, 'ranking' => null],
            ['name' => 'Government Polytechnic College, Paonta Sahib', 'city' => 'Sirmour', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gppaontasahib.in', 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Government Degree College, Paonta Sahib', 'city' => 'Sirmour', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdcpaontasahib.edu.in', 'logo' => null, 'established_year' => 1994, 'ranking' => null],
            ['name' => 'Government Degree College, Rajgarh', 'city' => 'Sirmour', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcrajgarh.ac.in', 'logo' => null, 'established_year' => 1980, 'ranking' => null],
            ['name' => 'IITT College of Engineering, Kala Amb', 'city' => 'Sirmour', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.iittcollege.com', 'logo' => 'https://iittcollege.com/wp-content/uploads/2021/03/logo.png', 'established_year' => 2009, 'ranking' => null],
            ['name' => 'Himalayan Institute of Dental Science, Paonta Sahib', 'city' => 'Sirmour', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.hids.ac.in', 'logo' => 'https://hids.ac.in/wp-content/uploads/2021/05/logo.png', 'established_year' => 2005, 'ranking' => null],
            ['name' => 'Government Degree College, Shillai', 'city' => 'Sirmour', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdcshillai.ac.in', 'logo' => null, 'established_year' => 1999, 'ranking' => null],
            ['name' => 'Government Degree College, Sangrah', 'city' => 'Sirmour', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcsangrah.in', 'logo' => null, 'established_year' => 2006, 'ranking' => null],

            ['name' => 'Lala Lajpat Rai University of Veterinary and Animal Sciences, Hisar (near Kangra)', 'city' => 'Kangra', 'type' => 'public', 'state' => null, 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Dr. Rajendra Prasad Government Medical College (RPGMC), Tanda', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.rpgmc.ac.in', 'logo' => 'https://www.rpgmc.ac.in/img/rpgmc-logo.png', 'established_year' => 1996, 'ranking' => null],
            ['name' => 'Vaishno College of Engineering', 'city' => 'Kangra', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.vaishno.edu.in', 'logo' => 'https://www.vaishno.edu.in/images/logo.png', 'established_year' => 2010, 'ranking' => null],
            ['name' => 'Central University of Himachal Pradesh (CUHP), Dharamshala', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.cuhimachal.ac.in', 'logo' => 'https://cuhimachal.ac.in/img/logo.png', 'established_year' => 2009, 'ranking' => null],
            ['name' => 'Chaudhary Sarwan Kumar Himachal Pradesh Krishi Vishvavidyalaya (CSKHPKV), Palampur', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.hillagric.ac.in', 'logo' => 'https://www.hillagric.ac.in/images/university.gif', 'established_year' => 1978, 'ranking' => null],
            ['name' => 'Rajiv Gandhi Government Engineering College (RGGEC), Nagrota Bagwan', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.rggec.ac.in', 'logo' => 'https://www.rggec.ac.in/img/logo.png', 'established_year' => 2014, 'ranking' => null],
            ['name' => 'Government College of Teacher Education (GCTE), Dharamshala', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gctedharamshala.org', 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Government Post Graduate College, Dharamshala', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcdharamshala.ac.in', 'logo' => 'https://www.gcdharamshala.ac.in/wp-content/themes/college/images/logo.png', 'established_year' => 1926, 'ranking' => null],
            ['name' => 'Arni University, Kathgarh', 'city' => 'Kangra', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.arni.in', 'logo' => 'https://arni.in/wp-content/themes/arni/images/logo.png', 'established_year' => 2009, 'ranking' => null],
            ['name' => 'Sri Sai University, Palampur', 'city' => 'Kangra', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.srisaiuniversity.com', 'logo' => 'https://www.srisaiuniversity.com/img/ssu.png', 'established_year' => 2010, 'ranking' => null],
            ['name' => 'Mehar Chand Mahajan DAV College', 'city' => 'Kangra', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.davkangra.in', 'logo' => null, 'established_year' => 1975, 'ranking' => null],
            ['name' => 'Himachal Institute of Engineering and Technology (HIET), Shahpur', 'city' => 'Kangra', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.hiet.ac.in', 'logo' => null, 'established_year' => 2010, 'ranking' => null],
            ['name' => 'Government Degree College, Baijnath', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcbaijnath.com', 'logo' => null, 'established_year' => 1970, 'ranking' => null],
            ['name' => 'Government Degree College, Dada Siba', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdcdadasiba.ac.in', 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Government Degree College, Indora', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcindora.in', 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Government Degree College, Dehra', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdcdehra.in', 'logo' => null, 'established_year' => 2017, 'ranking' => null],
            ['name' => 'Government Degree College, Rakkar', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdcrakkar.ac.in', 'logo' => null, 'established_year' => 2016, 'ranking' => null],
            ['name' => 'Government Polytechnic', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gpkangra.com', 'logo' => 'https://www.gpkangra.com/images/govt-polytechnic.png', 'established_year' => 1962, 'ranking' => null],
            ['name' => 'National Institute of Fashion Technology (NIFT)', 'city' => 'Kangra', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.nift.ac.in/kangra', 'logo' => 'https://www.nift.ac.in/themes/site/default/images/nift_logo.png', 'established_year' => 2009, 'ranking' => null],

            ['name' => 'Baba Ghulam Shah Badshah University, Rajouri (near Chamba)', 'city' => 'Chamba', 'type' => 'public', 'state' => null, 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Pt. Jawahar Lal Nehru Government Medical College', 'city' => 'Chamba', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gmcchamba.edu.in', 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Government Millennium Polytechnic College', 'city' => 'Chamba', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gmpchamba.edu.in', 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Government Degree College, Chamba', 'city' => 'Chamba', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcchamba.edu.in', 'logo' => 'https://gcchamba.edu.in/wp-content/themes/college/images/logo.png', 'established_year' => 1958, 'ranking' => null],
            ['name' => 'Government Degree College, Chowari', 'city' => 'Chamba', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcchowari.edu.in', 'logo' => null, 'established_year' => 1994, 'ranking' => null],
            ['name' => 'Government Degree College, Bharmour', 'city' => 'Chamba', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcbharmour.com', 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Government Degree College, Telka', 'city' => 'Chamba', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdctelka.com', 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Chamba Millennium B.Ed College', 'city' => 'Chamba', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'http://cmes.co.in/index.php/home-college', 'logo' => null, 'established_year' => 2007, 'ranking' => null],
            ['name' => 'Shakuntla Memorial College of Nursing', 'city' => 'Chamba', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => 'www.shakuntlanursingcollege.in', 'logo' => null, 'established_year' => null, 'ranking' => null],

            ['name' => 'Thakur Sen Negi Government College, Reckong Peo', 'city' => 'Kinnaur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.tsnegigcreckongpeo.ac.in', 'logo' => null, 'established_year' => 1994, 'ranking' => null],
            ['name' => 'Government Degree College, Sangla', 'city' => 'Kinnaur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => 2017, 'ranking' => null],
            ['name' => 'Government Degree College, Nichar', 'city' => 'Kinnaur', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => 2017, 'ranking' => null],
            ['name' => 'Him Institute of Education, Nichar', 'city' => 'Kinnaur', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => 2006, 'ranking' => null],

            ['name' => 'Jawahar Lal Nehru Government Degree College, Haripur, Manali', 'city' => 'Kullu', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.jlngcharipurmanali.ac.in', 'logo' => null, 'established_year' => 1982, 'ranking' => null],
            ['name' => 'Government College', 'city' => 'Kullu', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gckullu.ac.in', 'logo' => 'https://www.gckullu.ac.in/images/college-logo.png', 'established_year' => 1967, 'ranking' => null],
            ['name' => 'Government Degree College, Banjar', 'city' => 'Kullu', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdcbanjar.edu.in', 'logo' => null, 'established_year' => 1999, 'ranking' => null],
            ['name' => 'Government Degree College, Nirmand', 'city' => 'Kullu', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gcnirmand.edu.in', 'logo' => null, 'established_year' => 2016, 'ranking' => null],
            ['name' => 'Government Degree College, Sainj', 'city' => 'Kullu', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gdcsainj.edu.in', 'logo' => null, 'established_year' => 2016, 'ranking' => null],
            ['name' => 'Government Polytechnic College, Kullu', 'city' => 'Kullu', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'www.gpkullu.edu.in', 'logo' => 'https://www.gpkullu.edu.in/images/logo.png', 'established_year' => null, 'ranking' => null],
            ['name' => 'Christian Nursing College, Dhalpur', 'city' => 'Kullu', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Kullu College of Education, Garsa', 'city' => 'Kullu', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Rameshwari Teacher Training Institute, Bhuntar', 'city' => 'Kullu', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Vinayaka College of Pharmacy', 'city' => 'Kullu', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Heritage Art College, Naggar', 'city' => 'Kullu', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Manali Institute Of Hospitality Management, Manali', 'city' => 'Kullu', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Bharat Institute Information Technology (BIIT)', 'city' => 'Kullu', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Life Secure Institute of Hotel Management', 'city' => 'Kullu', 'type' => 'private', 'state' => 'Himachal Pradesh', 'website' => null, 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Government Degree College, Ani', 'city' => 'Kullu', 'type' => 'public', 'state' => 'Himachal Pradesh', 'website' => 'https://gcanni.edu.in/', 'logo' => null, 'established_year' => null, 'ranking' => null],
            ['name' => 'Indian Institute of Science (IISc)', 'city' => 'Bengaluru', 'type' => 'public', 'state' => 'Karnataka', 'website' => 'www.iisc.ac.in', 'logo' => 'https://www.iisc.ac.in/wp-content/uploads/2021/08/logo-blue.svg', 'established_year' => 1909, 'ranking' => 1],
            ['name' => 'Indian Institute of Technology (IIT) Madras', 'city' => 'Chennai', 'type' => 'public', 'state' => 'Tamil Nadu', 'website' => 'www.iitm.ac.in', 'logo' => 'https://www.iitm.ac.in/sites/all/themes/iitmadras/images/logo.png', 'established_year' => 1959, 'ranking' => 1],
            ['name' => 'All India Institute of Medical Sciences (AIIMS), Delhi', 'city' => 'New Delhi', 'type' => 'public', 'state' => 'Delhi', 'website' => 'www.aiims.edu', 'logo' => 'https://www.aiims.edu/images/logo.png', 'established_year' => 1956, 'ranking' => 1],
            ['name' => 'Indian Institute of Technology (IIT) Delhi', 'city' => 'New Delhi', 'type' => 'public', 'state' => 'Delhi', 'website' => 'www.iitd.ac.in', 'logo' => 'https://www.iitd.ac.in/sites/all/themes/iitd/logo.png', 'established_year' => 1961, 'ranking' => 2],
            ['name' => 'Indian Institute of Technology (IIT) Bombay', 'city' => 'Mumbai', 'type' => 'public', 'state' => 'Maharashtra', 'website' => 'www.iitb.ac.in', 'logo' => 'https://www.iitb.ac.in/sites/all/themes/iitb/logo.png', 'established_year' => 1958, 'ranking' => 3],
            ['name' => 'Indian Institute of Technology (IIT) Kanpur', 'city' => 'Kanpur', 'type' => 'public', 'state' => 'Uttar Pradesh', 'website' => 'www.iitk.ac.in', 'logo' => 'https://www.iitk.ac.in/themes/iitk/logo.png', 'established_year' => 1959, 'ranking' => 4],
            ['name' => 'Indian Institute of Technology (IIT) Kharagpur', 'city' => 'Kharagpur', 'type' => 'public', 'state' => 'West Bengal', 'website' => 'www.iitkgp.ac.in', 'logo' => 'https://www.iitkgp.ac.in/sites/default/files/iitkgp_logo.png', 'established_year' => 1951, 'ranking' => 5],
            ['name' => 'Jawaharlal Nehru University (JNU)', 'city' => 'New Delhi', 'type' => 'public', 'state' => 'Delhi', 'website' => 'www.jnu.ac.in', 'logo' => 'https://www.jnu.ac.in/images/logo.png', 'established_year' => 1969, 'ranking' => 2],
            ['name' => 'Banaras Hindu University (BHU)', 'city' => 'Varanasi', 'type' => 'public', 'state' => 'Uttar Pradesh', 'website' => 'www.bhu.ac.in', 'logo' => 'https://www.bhu.ac.in/bhu_logo.png', 'established_year' => 1916, 'ranking' => 6],
            ['name' => 'University of Delhi (DU)', 'city' => 'New Delhi', 'type' => 'public', 'state' => 'Delhi', 'website' => 'www.du.ac.in', 'logo' => 'https://www.du.ac.in/themes/du/logo.png', 'established_year' => 1922, 'ranking' => 5],
            ['name' => 'Jadavpur University', 'city' => 'Kolkata', 'type' => 'public', 'state' => 'West Bengal', 'website' => 'www.jaduniv.edu.in', 'logo' => 'https://www.jaduniv.edu.in/images/logo.png', 'established_year' => 1955, 'ranking' => 9],
            ['name' => 'Vellore Institute of Technology (VIT)', 'city' => 'Vellore', 'type' => 'private', 'state' => 'Tamil Nadu', 'website' => 'www.vit.ac.in', 'logo' => 'https://www.vit.ac.in/sites/all/themes/custom/vit/logo.png', 'established_year' => 1984, 'ranking' => 19],
            ['name' => 'Manipal Academy of Higher Education (MAHE)', 'city' => 'Manipal', 'type' => 'deemed', 'state' => 'Karnataka', 'website' => 'www.manipal.edu', 'logo' => 'https://www.manipal.edu/content/dam/manipal/mahe/mahe-logo.png', 'established_year' => 1993, 'ranking' => 14],
            ['name' => 'Amrita Vishwa Vidyapeetham', 'city' => 'Coimbatore', 'type' => 'private', 'state' => 'Tamil Nadu', 'website' => 'www.amrita.edu', 'logo' => 'https://www.amrita.edu/assets/logo.png', 'established_year' => 2003, 'ranking' => 18],
            ['name' => 'Birla Institute of Technology & Science (BITS) Pilani', 'city' => 'Pilani', 'type' => 'private', 'state' => 'Rajasthan', 'website' => 'www.bits-pilani.ac.in', 'logo' => 'https://www.bits-pilani.ac.in/uploads/logo-bits-pilani.png', 'established_year' => 1964, 'ranking' => 7],
            ['name' => 'Indian Institute of Management (IIM) Ahmedabad', 'city' => 'Ahmedabad', 'type' => 'public', 'state' => 'Gujarat', 'website' => 'www.iima.ac.in', 'logo' => 'https://www.iima.ac.in/sites/all/themes/iima/logo.png', 'established_year' => 1961, 'ranking' => 1],
            ['name' => 'Indian Institute of Management (IIM) Bangalore', 'city' => 'Bengaluru', 'type' => 'public', 'state' => 'Karnataka', 'website' => 'www.iimb.ac.in', 'logo' => 'https://www.iimb.ac.in/themes/iimb_theme/logo.png', 'established_year' => 1973, 'ranking' => 2],
            ['name' => 'Hindu College, Delhi', 'city' => 'New Delhi', 'type' => 'public', 'state' => 'Delhi', 'website' => 'www.hinducollege.ac.in', 'logo' => 'https://www.hinducollege.ac.in/images/logo.png', 'established_year' => 1899, 'ranking' => 1],
            ['name' => 'St. Stephen\'s College, Delhi', 'city' => 'New Delhi', 'type' => 'public', 'state' => 'Delhi', 'website' => 'www.ststephens.edu', 'logo' => 'https://www.ststephens.edu/wp-content/uploads/2019/07/st-stephens-college-logo.png', 'established_year' => 1881, 'ranking' => 5],
            ['name' => 'Loyola College', 'city' => 'Chennai', 'type' => 'private', 'state' => 'Tamil Nadu', 'website' => 'www.loyolacollege.edu', 'logo' => 'https://www.loyolacollege.edu/img/logo.png', 'established_year' => 1925, 'ranking' => 14],
            ['name' => 'Miranda House', 'city' => 'New Delhi', 'type' => 'public', 'state' => 'Delhi', 'website' => 'www.mirandahouse.ac.in', 'logo' => 'https://www.mirandahouse.ac.in/wp-content/themes/miranda/images/logo.png', 'established_year' => 1948, 'ranking' => 2],
            ['name' => 'Fergusson College', 'city' => 'Pune', 'type' => 'autonomous', 'state' => 'Maharashtra', 'website' => 'www.fergusson.edu', 'logo' => 'https://www.fergusson.edu/img/logo.png', 'established_year' => 1885, 'ranking' => 57],
            ['name' => 'Presidency College, Chennai', 'city' => 'Chennai', 'type' => 'public', 'state' => 'Tamil Nadu', 'website' => 'www.presidencychennai.com', 'logo' => null, 'established_year' => 1840, 'ranking' => 15],
            ['name' => 'Lady Shri Ram College for Women (LSR), Delhi', 'city' => 'New Delhi', 'type' => 'public', 'state' => 'Delhi', 'website' => 'www.lsr.edu.in', 'logo' => 'https://www.lsr.edu.in/images/logo.png', 'established_year' => 1956, 'ranking' => 17],
            ['name' => 'Shri Ram College of Commerce (SRCC), Delhi', 'city' => 'New Delhi', 'type' => 'public', 'state' => 'Delhi', 'website' => 'www.srcc.edu', 'logo' => 'https://www.srcc.edu/sites/all/themes/srcc/logo.png', 'established_year' => 1926, 'ranking' => 18],
            ['name' => 'Hansraj College, Delhi', 'city' => 'New Delhi', 'type' => 'public', 'state' => 'Delhi', 'website' => 'www.hansrajcollege.ac.in', 'logo' => 'https://www.hansrajcollege.ac.in/images/logo.png', 'established_year' => 1948, 'ranking' => 3],
            ['name' => 'Mumbai University', 'city' => 'Mumbai', 'type' => 'public', 'state' => 'Maharashtra', 'website' => 'www.mu.ac.in', 'logo' => null, 'established_year' => 1851, 'ranking' => 4],
            ['name' => 'Aligarh Muslim University (AMU)', 'city' => 'Aligarh', 'type' => 'public', 'state' => 'Uttar Pradesh', 'website' => 'www.amu.ac.in', 'logo' => 'https://www.amu.ac.in/images/amu-logo.png', 'established_year' => 1920, 'ranking' => 8],
        ];

        $universityIds = [];
        foreach ($universities as $uni) {
            $universityId = DB::table('universities')->insertGetId([
                'name' => $uni['name'],
                'slug' => Str::slug($uni['name']),
                'city' => $uni['city'],
                'state' => $uni['state'] ?? 'Himachal Pradesh',
                'country_id' => $countryIdIndia,
                'type' => $uni['type'],
                'website' => $uni['website'] ?? null,
                'logo' => $uni['logo'] ?? null,
                'established_year' => $uni['established_year'] ?? null,
                'ranking' => $uni['ranking'] ?? null,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $universityIds[$uni['name']] = $universityId; // FIXED: Store just the ID, not an array
        }


        // --- City-based Custom Locations (type: custom) ---
        foreach ($cities as $city) {
            DB::table('locations')->insert([
                'type' => 'custom',
                'name' => $city['name'],
                'address' => $city['name'] . ', ' . $city['state'],
                'city_id' => $cityIds[$city['name']], // FIXED: Remove ['id'] - cityIds already contains the ID
                'country_id' => $countryIdIndia,
                'latitude' => $city['latitude'],
                'longitude' => $city['longitude'],
                'description' => "Popular location in " . $city['name'] . " for meetups and exchanges",
                'is_active' => true,
                'is_popular' => true,
                'is_safe_meetup' => true,
                'popularity_score' => rand(50, 100),
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        // --- Campus Locations (type: campus) with university_id ---
        foreach ($universities as $uni) {
            DB::table('locations')->insert([
                'type' => 'campus',
                'name' => $uni['name'],
                'address' => $uni['name'] . ', ' . $uni['city'],
                'city_id' => isset($cityIds[$uni['city']]) ? $cityIds[$uni['city']] : null, // FIXED: Check if city exists and get correct ID
                'country_id' => $countryIdIndia,
                'university_id' => $universityIds[$uni['name']], // FIXED: Remove ['id'] - universityIds already contains the ID
                'latitude' => null,
                'longitude' => null,
                'description' => "Campus location for " . $uni['name'],
                'is_active' => true,
                'is_popular' => true,
                'is_safe_meetup' => true,
                'popularity_score' => rand(70, 95),
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
    }
}
