<?php

namespace Tests\App\App\Http\Controllers;

class TestV1Controller extends Controller
{
    public function index()
    {
        return "VERSION 1 INDEX";
    }

    public function store()
    {
        return "VERSION 1 STORE";
    }

    public function show($id)
    {
        return "VERSION 1 SHOW $id";
    }
}
