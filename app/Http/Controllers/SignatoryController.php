<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Signatory;

class SignatoryController extends Controller
{
    private function headOfAgency(): ?Signatory
    {
        return Signatory::query()->orderBy('id')->first();
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'office_address' => 'required|string|max:500',
        ]);
    }

    private function collapseToSingleRecord(Signatory $signatory): void
    {
        Signatory::query()
            ->where('id', '!=', $signatory->id)
            ->delete();
    }

    public function index()
    {
        $signatory = $this->headOfAgency();

        return view('admin.signatories.index', compact('signatory'));
    }

    public function create()
    {
        if ($signatory = $this->headOfAgency()) {
            return redirect()
                ->route('signatories.edit', $signatory->id)
                ->with('info', 'Only one Regional Director can be configured.');
        }

        return view('admin.signatories.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPayload($request);

        $signatory = $this->headOfAgency();

        if ($signatory) {
            $signatory->update($validated);
            $message = 'Regional Director updated successfully.';
        } else {
            $signatory = Signatory::create($validated);
            $message = 'Regional Director saved successfully.';
        }

        $this->collapseToSingleRecord($signatory);

        return redirect()->route('signatories.index')
            ->with('success', $message);
    }

    public function edit($id)
    {
        $signatory = $this->headOfAgency();

        if (!$signatory) {
            return redirect()->route('signatories.create');
        }

        if ((int) $signatory->id !== (int) $id) {
            return redirect()->route('signatories.edit', $signatory->id);
        }

        return view('admin.signatories.edit', compact('signatory'));
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validatedPayload($request);

        $signatory = Signatory::findOrFail($id);
        $signatory->update($validated);
        $this->collapseToSingleRecord($signatory);

        return redirect()->route('signatories.index')
            ->with('success', 'Regional Director updated successfully.');
    }

    public function show($id)
    {
        return redirect()->route('signatories.index');
    }

    public function destroy($id)
    {
        $signatory = Signatory::findOrFail($id);
        $signatory->delete();

        return redirect()->route('signatories.index')
            ->with('success', 'Regional Director record removed successfully.');
    }
}
