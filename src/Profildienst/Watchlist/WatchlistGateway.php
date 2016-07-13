<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 05.06.16
 * Time: 22:19
 */

namespace Profildienst\Watchlist;


interface WatchlistGateway {

    public function getWatchlistData($watchlistId);
    public function getWatchlistTitleCount($watchlistId);
    public function getWatchlistTitles($watchlistId, $page);
    public function getWatchlists();
    public function updateTitlesWatchlist(array $ids, $watchlistId);
    public function createWatchlist(array $watchlistData);
    public function deleteWatchlist($watchlistId);
    public function renameWatchlist($watchlistId, $name);
    public function updateDefaultWatchlist($watchlistId);
    public function removeAllTitlesFromWatchlist($watchlistId);
    public function updateWatchlists(array $watchlists);
}