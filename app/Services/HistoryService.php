<?php

namespace App\Services;

use App\Models\UserHistory;

class HistoryService
{
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
