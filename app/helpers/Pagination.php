<?php
/**
 * Helper de pagination
 */
class Pagination {
    private $totalItems;
    private $itemsPerPage;
    private $currentPage;
    private $totalPages;
    
    public function __construct($totalItems, $itemsPerPage = 15, $currentPage = 1) {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = max(1, (int)$currentPage);
        $this->totalPages = max(1, ceil($totalItems / $itemsPerPage));
        
        // S'assurer que la page courante ne dépasse pas le total
        if ($this->currentPage > $this->totalPages) {
            $this->currentPage = $this->totalPages;
        }
    }
    
    public function getOffset() {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }
    
    public function getLimit() {
        return $this->itemsPerPage;
    }
    
    public function getCurrentPage() {
        return $this->currentPage;
    }
    
    public function getTotalPages() {
        return $this->totalPages;
    }
    
    public function getTotalItems() {
        return $this->totalItems;
    }
    
    public function hasPreviousPage() {
        return $this->currentPage > 1;
    }
    
    public function hasNextPage() {
        return $this->currentPage < $this->totalPages;
    }
    
    public function getPageNumbers($maxVisible = 5) {
        $pages = [];
        $half = floor($maxVisible / 2);
        
        $start = max(1, $this->currentPage - $half);
        $end = min($this->totalPages, $start + $maxVisible - 1);
        
        // Ajuster le début si on est proche de la fin
        if ($end - $start + 1 < $maxVisible) {
            $start = max(1, $end - $maxVisible + 1);
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }
        
        return $pages;
    }
    
    /**
     * Génère l'URL avec les paramètres de filtres préservés
     */
    public static function buildUrl($baseUrl, $params, $page) {
        $params['page'] = $page;
        $query = http_build_query($params);
        return $baseUrl . '?' . $query;
    }
    
    /**
     * Retourne les informations pour la vue
     */
    public function getInfo() {
        return [
            'currentPage' => $this->currentPage,
            'totalPages' => $this->totalPages,
            'totalItems' => $this->totalItems,
            'itemsPerPage' => $this->itemsPerPage,
            'offset' => $this->getOffset(),
            'hasPrevious' => $this->hasPreviousPage(),
            'hasNext' => $this->hasNextPage(),
            'pages' => $this->getPageNumbers(),
            'startItem' => $this->totalItems > 0 ? $this->getOffset() + 1 : 0,
            'endItem' => min($this->getOffset() + $this->itemsPerPage, $this->totalItems)
        ];
    }
}
