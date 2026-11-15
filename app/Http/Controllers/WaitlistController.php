<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Handles waitlist requests.
 */
class WaitlistController extends Controller
{
    public function store(Request $request)
    {
        $d = $r->validate([
            'email' => 'required|email|max:190',
            'interest' => 'required|in:pro,avulso,nao_sei',
        ]);

        DB::table('waitlist')->updateOrInsert(
            ['email' => $d['email']],
            ['interest' => $d['interest'], 'created_at' => now(), 'updated_at' => now()]
        );

        return redirect(route('home').'#lista-de-espera')
            ->with('waitlist', 'Pronto! Avisamos você por e-mail.');
    }
}
