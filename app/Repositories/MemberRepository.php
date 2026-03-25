<?php
namespace App\Repositories;

use App\Models\Member;
use App\Repositories\Contracts\MemberRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MemberRepository implements MemberRepositoryInterface
{
    public function all(): Collection
    {
        return Member::latest()->get();
    }

    public function find(int $id): ?Member
    {
        return Member::find($id);
    }

    public function findWithDetails(int $id): Member
    {
        return Member::with([
            'points' => fn($q) => $q->latest()->take(20),
            'transactions' => fn($q) => $q->latest()->take(10),
        ])->findOrFail($id);
    }

    public function create(array $data): Member
    {
        return Member::create($data);
    }

    public function update(Member $member, array $data): Member
    {
        $member->update($data);
        return $member->fresh();
    }

    public function delete(Member $member): bool
    {
        return $member->delete();
    }

    public function search(string $query, int $limit = 10): Collection
    {
        return Member::active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('phone_number', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();
    }

    public function active(): Collection
    {
        return Member::active()->get();
    }
}
