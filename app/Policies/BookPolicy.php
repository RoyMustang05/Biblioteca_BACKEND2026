<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    private function canRead(User $user, string $permission): bool
    {
        return $user->hasLibraryRole() && $user->can($permission);
    }

    private function canManage(User $user, string $permission): bool
    {
        return $user->isBibliotecario() && $user->can($permission);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->canRead($user, 'books.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Book $book): bool
    {
        return $this->canRead($user, 'books.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->canManage($user, 'books.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Book $book): bool
    {
        return $this->canManage($user, 'books.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Book $book): bool
    {
        return $this->canManage($user, 'books.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Book $book): bool
    {
        return $this->canManage($user, 'books.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Book $book): bool
    {
        return $this->canManage($user, 'books.forceDelete');
    }
}
