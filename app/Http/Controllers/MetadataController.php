<?php

namespace App\Http\Controllers;

use App\Unit;
use App\Category;
use Illuminate\Http\Request;

class MetadataController extends Controller
{
    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function units()
    {
        return view('units');
    }
    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function categories()
    {
        return view('categories');
    }
}
