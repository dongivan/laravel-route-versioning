<?php

namespace Tests\App\App\Http\Controllers;

class TestV1_10Controller extends Controller
{
    public function index()
    {
        return "VERSION 1.10 INDEX";
    }

    public function store()
    {
        return "VERSION 1.10 STORE";
    }

    public function show($id)
    {
        return "VERSION 1.10 SHOW $id";
    }
}
