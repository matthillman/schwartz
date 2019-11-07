<?php

namespace App\Util;

class SchwartzSheetData {

    private $service;
    private $data;
    private $parsed;

    public function __construct($skipRefresh = false) {
        $this->service = google_sheets();
        $this->parsed = collect();

        if (!$skipRefresh) {
            $this->refreshData();
        }
    }

    public function refreshData() {
        $this->data = collect($this->service->spreadsheets_values->get(config('google.sheet_id'), config('google.sheet_range')));

        $headings = collect($this->data->shift())->map(function($v) { return strtolower($v); });

        $this->parsed = collect($this->data)->map(function($row) use ($headings) {
            return $headings->combine($row);
        })->keyBy('guild_id');
    }

    public function data($guild_id = null) {
        if (is_null($guild_id)) {
            return $this->parsed;
        } else {
            return $this->parsed->get($guild_id);
        }
    }
}