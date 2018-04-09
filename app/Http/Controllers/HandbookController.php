<?php

namespace App\Http\Controllers;

use Storage;
use Markdown;
use Illuminate\Http\Request;

class HandbookController extends Controller
{
    public function __invoke($name) {
        $dropbox = Storage::disk('dropbox');

        $raw = $dropbox->get('guides/' . str_finish($name, '.guide.md'));

        $handbook = collect(explode("\n", $raw));

        $sections = $handbook->reduce(function($sections, $line) use ($dropbox) {
            if (starts_with($line, '@')) {
                $file = $dropbox->get(str_finish(trim(str_replace_first('@', '', $line)), '.md'));
                $sections[] = $file;
                $sections[] = "";
            } else {
                $sections[count($sections) - 1] = implode("\n", [last($sections), $line]);
            }

            return $sections;
        }, [""])->filter(function($section) {
            return strlen($section) > 0;
        })->map(function($section) {
            return Markdown::convertToHtml($section);
        });

        return view('handbook', ['sections' => $sections]);
    }
}
;