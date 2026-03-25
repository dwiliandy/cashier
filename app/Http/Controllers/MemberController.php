<?php
namespace App\Http\Controllers;
use App\Services\MemberService;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(
        private MemberService $memberService,
    ) {}

    public function index()
    {
        $members = $this->memberService->getAll();
        return view('members.index', compact('members'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:members,phone_number',
            'email' => 'nullable|email',
        ]);
        $this->memberService->create($request->only('name', 'phone_number', 'email'));
        return back()->with('success', 'Member berhasil ditambahkan!');
    }

    public function update(Request $request, \App\Models\Member $member)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:members,phone_number,' . $member->id,
            'email' => 'nullable|email',
        ]);
        $this->memberService->update($member, $request->only('name', 'phone_number', 'email'));
        return back()->with('success', 'Member berhasil diperbarui!');
    }

    public function show(\App\Models\Member $member)
    {
        $member = $this->memberService->find($member->id);
        return view('members.show', compact('member'));
    }

    public function destroy(\App\Models\Member $member)
    {
        $this->memberService->delete($member);
        return redirect()->route('members.index')->with('success', 'Member berhasil dihapus!');
    }

    // API for POS member lookup
    public function apiSearch(Request $request)
    {
        return response()->json($this->memberService->search($request->q));
    }

    public function apiAll()
    {
        return response()->json($this->memberService->getActive());
    }
}
