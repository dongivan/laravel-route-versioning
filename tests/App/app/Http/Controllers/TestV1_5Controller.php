<?php

namespace Tests\App\App\Http\Controllers;

class TestV1_5Controller extends Controller
{
    public function index()
    {
        return "VERSION 1.5 INDEX";
    }

    public function store()
    {
        return "VERSION 1.5 STORE";
    }

    public function show($id)
    {
        return "VERSION 1.5 SHOW $id";
    }
}
