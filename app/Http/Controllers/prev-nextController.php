<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Test;
/*
class prev_nextController extends Controller
{
    public function step1() {
        return view('step1');
    }

    public function postStep1(Request $request) {
        $request->validate(
                ['name' => 'required|string|max:100'],

                );
        session(['form.name' => $request->input('name')]);
        return redirect()->route('form.step2');
    }

    public function step2() {
        return view('step2');
    }

    public function postStep2(Request $request) {
        $request->validate(['age' => 'required|integer|max:120']);
        session(['form.age' => $request->input('age')]);
        return redirect()->route('form.step3');
    }

    public function step3() {
        return view('step3');
    }

    public function postStep3(Request $request) {
        $request->validate(['email' => 'required|email']);
        session(['form.email' => $request->input('email')]);
        return redirect()->route('form.confirm');
    }

    public function confirm() {
        $data = session('form');
        return view('confirm', compact('data'));
    }

    public function submit(Request $request) {
        $data = session('form');

        Test::create([
            'name' => $data['name'],
            'age' => $data['age'],
            'email' => $data['email'],
        ]);

        session()->forget('form');
        return redirect()->route('form.step1')->with('success', 'Form submitted successfully!');
    }
}
*/