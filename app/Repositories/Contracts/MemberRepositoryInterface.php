<?php
namespace App\Repositories\Contracts;

use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;

interface MemberRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Member;
    public function findWithDetails(int $id): Member;
    public function create(array $data): Member;
    public function update(Member $member, array $data): Member;
    public function delete(Member $member): bool;
    public function search(string $query, int $limit = 10): Collection;
    public function active(): Collection;
}
