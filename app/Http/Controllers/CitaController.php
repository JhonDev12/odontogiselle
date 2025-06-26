<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CitaController extends Controller
{
 public function index()
 {
     // Logic to retrieve and display appointments
 }

 public function create()
 {
     // Logic to show form for creating a new appointment
 }

 public function store(Request $request)
 {
     // Logic to store a new appointment
 }
    public function show($id)
    {
        // Logic to display a specific appointment
    }

    public function update(Request $request, $id)
    {
        // Logic to update a specific appointment
    }

    public function destroy($id)
    {
        // Logic to delete a specific appointment
    }
}
