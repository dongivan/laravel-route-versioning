<?php

namespace Tests\App\App\Http\Controllers;

class TestV2Controller extends Controller
{
    public function index()
    {
        return "VERSION 2 INDEX";
    }

    public function store()
    {
        return "VERSION 2 STORE";
    }

    public function show($id)
    {
        return "VERSION 2 SHOW $id";
    }
}
