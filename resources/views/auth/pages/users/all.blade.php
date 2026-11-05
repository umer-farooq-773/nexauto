@php
    use App\Constants\Constants;
    $roleSlug = request()->route('user');
    $isAggregateList = $roleSlug === Constants::USER;
    $roleLabel = $isAggregateList ? 'User' : \Str::title(str_replace('_', ' ', $roleSlug));
    $pageTitle = $isAggregateList ? 'User List' : $roleLabel . ' ' . __('language.list');
    $pageSubtitle = $isAggregateList
        ? 'Manage all user accounts from this page'
        : 'Manage ' . $roleLabel . ' accounts from this page';
    $directoryTitle = $isAggregateList ? 'User Directory' : $roleLabel . ' Directory';
    $totalDescription = $isAggregateList ? 'Total Users' : 'Total ' . $roleLabel;
    $users = $data['all']->loadMissing('roles');
    $listRoute = route('users.index', ['user' => $roleSlug]);

    $totalUsers = $users->count();
    $verifiedUsers = $users->whereNotNull('email_verified_at')->count();
    $unverifiedUsers = $totalUsers - $verifiedUsers;
    $usersWithProfile = $users->filter(fn ($user) => filled($user->profile()))->count();
@endphp

<x-layouts.auth page-title="{{ $pageTitle }}" sub-title="{{ $pageSubtitle }}">

    <x-slot:actionButton>
        @can('add_user')
            <x-auth.href-link
                link-href="{{ route('users.create') }}"
                link-class="btn btn-primary"
                link-value="{{ __('language.create_new_staff_label') }}" />
        @endcan
    </x-slot:actionButton>

    <x-slot:quickLinks>
        <x-auth.quick-link-card-small
            icon="fa-users"
            title="{{ $totalUsers }}"
            description="{{ $totalDescription }}"
            :route="$listRoute" />

        <x-auth.quick-link-card-small
            icon="fa-user-check"
            title="{{ $verifiedUsers }}"
            description="Verified Accounts"
            :route="$listRoute" />

        <x-auth.quick-link-card-small
            icon="fa-user-clock"
            title="{{ $unverifiedUsers }}"
            description="Pending Verification"
            :route="$listRoute" />

        <x-auth.quick-link-card-small
            icon="fa-image-portrait"
            title="{{ $usersWithProfile }}"
            description="Profiles Added"
            :route="$listRoute" />
    </x-slot:quickLinks>

    <div class="bg-white mt-3">
        <x-all-list title="{{ $directoryTitle }}" :data="$data['all']">
            <x-auth.datatable :search="true">
                <thead>
                    <tr class="text-dark">
                        <th scope="col">#</th>
                        <th scope="col">User</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Verification</th>
                        @canany(['view_user', 'edit_user', 'delete_user'])
                            <th scope="col">Action</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>

                    @foreach ($users as $key => $user)
                        @php
                            $userRole = $user->roles->first()?->title ?? $user->roles->first()?->name ?? 'N/A';
                            $isVerified = filled($user->email_verified_at);
                        @endphp
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $user->profile() ?? 'https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_1280.png' }}"
                                        alt="{{ $user->full_name }}"
                                        onerror="this.onerror=null; this.src='https://cdn.pixabay.com/photo/2023/02/18/11/00/icon-7797704_1280.png'"
                                        class="rounded-circle border"
                                        style="width: 42px; height: 42px; object-fit: cover;">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $user->full_name }}</div>
                                        <small class="text-muted">User ID: {{ $user->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $userRole }}</span>
                            </td>
                            <td>
                                @if ($isVerified)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Verified</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                @endif
                            </td>

                            @canany(['view_user', 'edit_user', 'delete_user'])
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @can('view_user')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('users.show', $user->id) }}">
                                                        <i class="fa-solid fa-eye me-2 text-primary"></i> View
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('edit_user')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('users.edit', $user->id) }}">
                                                        <i class="fa-solid fa-pen-to-square me-2 text-success"></i> Edit
                                                    </a>
                                                </li>
                                            @endcan

                                            @can('delete_user')
                                                <li>
                                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                        onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fa-solid fa-trash me-2"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            @endcanany
                        </tr>
                    @endforeach
                </tbody>
            </x-auth.datatable>
        </x-all-list>
    </div>

</x-layouts.auth>
