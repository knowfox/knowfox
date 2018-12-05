<?php

namespace Knowfox\Policies;

use Knowfox\User;
use Knowfox\Models\Concept;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConceptPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the concept.
     *
     * @param  \Knowfox\User  $user
     * @param  \Knowfox\Models\Concept  $concept
     * @return mixed
     */
    public function view(User $user, Concept $concept)
    {
        return $user->id === $concept->owner_id
            || $concept->shares()
                ->where('user_id', $user->id)
                ->count() > 0;
    }

    /**
     * Determine whether the user can create concepts.
     *
     * @param  \Knowfox\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the concept.
     *
     * @param  \Knowfox\User  $user
     * @param  \Knowfox\Models\Concept  $concept
     * @return mixed
     */
    public function update(User $user, Concept $concept)
    {
        return $user->id === $concept->owner_id
            || $concept->shares()
                ->where('user_id', $user->id)
                ->wherePivot('permissions', 1)
                ->count() > 0;
    }

    /**
     * Determine whether the user can delete the concept.
     *
     * @param  \Knowfox\User  $user
     * @param  \Knowfox\Models\Concept  $concept
     * @return mixed
     */
    public function delete(User $user, Concept $concept)
    {
        return $user->id === $concept->owner_id;
    }
}
