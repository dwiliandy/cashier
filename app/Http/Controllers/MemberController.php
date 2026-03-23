<?php
namespace App\Http\Controllers;
use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members = Member::latest()->get();
        return view('members.index', compact('members'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:members,phone_number',
            'email' => 'nullable|email',
        ]);
        Member::create($request->only('name', 'phone_number', 'email'));
        return back()->with('success', 'Member berhasil ditambahkan!');
    }

    public function update(Request $request, Member $member)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:members,phone_number,' . $member->id,
            'email' => 'nullable|email',
        ]);
        $member->update($request->only('name', 'phone_number', 'email'));
        return back()->with('success', 'Member berhasil diperbarui!');
    }

    public function show(Member $member)
    {
        $member->load(['points' => fn($q) => $q->latest()->take(20), 'transactions' => fn($q) => $q->latest()->take(10)]);
        return view('members.show', compact('member'));
    }

    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->route('members.index')->with('success', 'Member berhasil dihapus!');
    }

    // API for POS member lookup
    public function apiSearch(Request $request)
    {
        $members = Member::active()
            ->when($request->q, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('phone_number', 'like', "%{$s}%"))
            ->limit(10)->get();
        return response()->json($members);
    }

    public function apiAll()
    {
        return response()->json(Member::active()->get());
    }
}
