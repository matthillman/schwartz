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
            if (starts_with($line, '#')) {
                $sections[] = [
                    'title' => str_replace_first('#', '', $line),
                    'content' => [],
                ];
            } else if (count($line) > 0) {
                $file = $dropbox->get(str_finish($line, '.md'));
                $converted = Markdown::convertToHtml($file);

                last($sections)['content'][] = $converted;
            }

            return $sections;
        }, []);

        return view('handbook', ['sections' => $sections]);
    }
}
