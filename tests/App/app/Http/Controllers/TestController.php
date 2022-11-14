<?php

namespace Tests\App\App\Http\Controllers;

class TestController extends Controller
{
    public function index()
    {
        return "NO VERSION INDEX";
    }

    public function store()
    {
        return "NO VERSION STORE";
    }

    public function show($id)
    {
        return "NO VERSION SHOW $id";
    }
}
