<?php
namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Member;
use App\Repositories\Contracts\MemberRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MemberService
{
    public function __construct(
        private MemberRepositoryInterface $memberRepo,
    ) {}

    public function getAll(): Collection
    {
        return $this->memberRepo->all();
    }

    public function find(int $id): Member
    {
        return $this->memberRepo->findWithDetails($id);
    }

    public function create(array $data): Member
    {
        $member = $this->memberRepo->create($data);
        ActivityLog::log('member_create', "Member '{$member->name}' ditambahkan", $member);
        return $member;
    }

    public function update(Member $member, array $data): Member
    {
        $updated = $this->memberRepo->update($member, $data);
        ActivityLog::log('member_update', "Member '{$updated->name}' diperbarui", $updated);
        return $updated;
    }

    public function delete(Member $member): bool
    {
        ActivityLog::log('member_delete', "Member '{$member->name}' dihapus", $member);
        return $this->memberRepo->delete($member);
    }

    public function search(?string $query): Collection
    {
        if (!$query) return $this->memberRepo->active();
        return $this->memberRepo->search($query);
    }

    public function getActive(): Collection
    {
        return $this->memberRepo->active();
    }
}
