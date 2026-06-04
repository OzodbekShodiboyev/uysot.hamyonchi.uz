<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $clients = Client::withCount('contracts')
            ->when($request->search, fn ($q) => $q->search($request->search))
            ->orderBy('full_name')
            ->paginate(20)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function show(Client $client): View
    {
        $client->load([
            'contracts.apartment.block',
            'contracts.payments',
            'contracts.manager',
        ]);

        return view('clients.show', compact('client'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'full_name'            => ['required', 'string', 'max:255'],
            'phone'                => ['required', 'string', 'max:20'],
            'phone_extra'          => ['nullable', 'string', 'max:20'],
            'passport_series'      => ['nullable', 'string', 'max:20'],
            'passport_issued_date' => ['nullable', 'date'],
            'passport_issued_by'   => ['nullable', 'string', 'max:255'],
            'pinfl'                => ['nullable', 'string', 'size:14'],
            'birth_date'           => ['nullable', 'date', 'before:today'],
            'address'              => ['nullable', 'string'],
            'workplace'            => ['nullable', 'string', 'max:255'],
            'position'             => ['nullable', 'string', 'max:255'],
            'notes'                => ['nullable', 'string'],
        ]);

        $client = Client::create($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'client' => $client]);
        }

        return redirect()
            ->route('clients.show', $client)
            ->with('success', "Mijoz {$client->full_name} qo'shildi.");
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'full_name'   => ['required', 'string', 'max:255'],
            'phone'       => ['required', 'string', 'max:20'],
            'phone_extra' => ['nullable', 'string', 'max:20'],
            'address'     => ['nullable', 'string'],
            'workplace'   => ['nullable', 'string', 'max:255'],
            'notes'       => ['nullable', 'string'],
        ]);

        $client->update($data);

        return back()->with('success', 'Mijoz ma\'lumotlari yangilandi.');
    }
}
