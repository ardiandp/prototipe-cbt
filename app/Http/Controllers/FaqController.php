<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * Display the FAQ knowledge base.
     */
    public function index(): View
    {
        $faqs = Faq::orderBy('order')->orderBy('id')->get();
        $faqsByCategory = $faqs->groupBy('category');
        $categories = Faq::CATEGORIES;

        return view('faq', compact('faqs', 'faqsByCategory', 'categories'));
    }

    /**
     * Store a newly created FAQ in storage (Admin only).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|in:' . implode(',', array_keys(Faq::CATEGORIES)),
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['order'] = $validated['order'] ?? 0;

        Faq::create($validated);

        return redirect()->route('faq')->with('success', 'Pertanyaan FAQ baru berhasil ditambahkan!');
    }

    /**
     * Update the specified FAQ in storage (Admin only).
     */
    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|in:' . implode(',', array_keys(Faq::CATEGORIES)),
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['order'] = $validated['order'] ?? 0;

        $faq->update($validated);

        return redirect()->route('faq')->with('success', 'Pertanyaan FAQ berhasil diperbarui!');
    }

    /**
     * Remove the specified FAQ from storage (Admin only).
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('faq')->with('success', 'Pertanyaan FAQ berhasil dihapus!');
    }
}
