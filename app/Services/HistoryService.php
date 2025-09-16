<?php

namespace App\Services;

use App\Models\UserHistory;

class HistoryService
{
    // General method (keep as backup)
    public static function addHistory($userId, $type, $action, $data = [])
    {
        return UserHistory::addHistory($userId, $type, $action, $data);
    }

    // Item-specific method (handles create, update, delete, sold, archive)
    public static function addItemHistory($userId, $itemId, $itemTitle, $action = 'create')
    {
        $actions = [
            'create' => 'Created',
            'update' => 'Updated', 
            'delete' => 'Deleted',
            'sold' => 'Sold',
            'archive' => 'Archived'
        ];

        $actionText = $actions[$action] ?? ucfirst($action);
        
        return UserHistory::addHistory($userId, $action, "{$actionText} item: {$itemTitle}", [
            'title' => "{$actionText}: {$itemTitle}",
            'category' => 'Item Management',
            'related_id' => $itemId,
            'related_type' => 'Item'
        ]);
    }

    // Promotion-specific method
    public static function addPromotionHistory($userId, $itemId, $itemTitle, $promotionType, $durationDays)
    {
        return UserHistory::addHistory($userId, 'promote', "Promoted item: {$itemTitle}", [
            'title' => "Promoted: {$itemTitle}",
            'category' => 'Promotion',
            'related_id' => $itemId,
            'related_type' => 'Item',
            'details' => [
                'promotion_type' => $promotionType,
                'duration_days' => $durationDays
            ]
        ]);
    }

    // Existing methods
    public static function addSearchHistory($userId, $query, $filters = [], $resultsCount = 0)
    {
        return UserHistory::addHistory($userId, 'search', "Searched for \"{$query}\"", [
            'title' => "Search: {$query}",
            'category' => 'Search',
            'details' => [
                'query' => $query,
                'filters' => $filters,
                'results_count' => $resultsCount
            ]
        ]);
    }

    public static function addViewHistory($userId, $itemId, $itemTitle)
    {
        return UserHistory::addHistory($userId, 'view', "Viewed \"{$itemTitle}\"", [
            'title' => "Viewed: {$itemTitle}",
            'category' => 'View',
            'related_id' => $itemId,
            'related_type' => 'Item'
        ]);
    }

    public static function addOfferHistory($userId, $itemId, $amount, $action = 'sent')
    {
        return UserHistory::addHistory($userId, 'offer', "Offer {$action}", [
            'title' => "Offer {$action}: ₹{$amount}",
            'category' => 'Offer',
            'related_id' => $itemId,
            'related_type' => 'Item',
            'details' => [
                'amount' => $amount,
                'action' => $action
            ]
        ]);
    }
}
