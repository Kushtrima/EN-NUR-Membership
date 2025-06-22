<x-app-layout>
    <div class="card">
        <!-- User Management Header Section -->
        <div style="
            background: linear-gradient(135deg, #1F6E38 0%, #28a745 100%); 
            color: white; 
            padding: 1.5rem 2rem; 
            border-radius: 12px; 
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(31, 110, 56, 0.2);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <div>
                    <h1 style="margin: 0; font-size: 1.8rem; font-weight: 700;">User Management</h1>
                    <p style="margin: 0; opacity: 0.9; font-size: 1rem;">Monitor all users, manage roles, and handle GDPR compliance</p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 2.5rem; font-weight: bold; margin: 0; line-height: 1;">{{ $users->total() }}</div>
                    <div style="font-size: 0.9rem; opacity: 0.9; margin: 0;">Total Users</div>
                </div>
            </div>
        </div>

        <!-- Enhanced Search Section -->
        <div style="background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 8px; padding: 2rem; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <svg style="width: 24px; height: 24px;" fill="#1F6E38" viewBox="0 0 24 24">
                    <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/>
                </svg>
                <h3 style="margin: 0; color: #1F6E38; font-size: 1.1rem;">Search Users</h3>
            </div>
            <div class="d-flex justify-content-center align-items-center">
                <form method="GET" action="{{ route('admin.users') }}" style="display: flex; gap: 0.75rem; align-items: center;">
                    <input type="text" name="search" class="form-control" placeholder="Search users by name, email..." 
                           value="{{ request('search') }}" 
                           style="width: 300px; padding: 0.75rem 1rem; border: 2px solid #dee2e6; border-radius: 6px; font-size: 1rem;">
                    
                    <button type="submit" class="search-btn" style="
                        background: #28a745; 
                        color: white; 
                        border: none; 
                        padding: 0.75rem 1.5rem; 
                        border-radius: 6px; 
                        cursor: pointer; 
                        font-size: 1rem; 
                        font-weight: 500;
                        display: flex; 
                        align-items: center; 
                        gap: 0.5rem;
                        transition: all 0.3s ease;
                        box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);">
                        <svg style="width: 18px; height: 18px;" fill="white" viewBox="0 0 24 24">
                            <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/>
                        </svg>
                        Search
                    </button>
                    
                    @if(request('search'))
                        <a href="{{ route('admin.users') }}" class="clear-btn" style="
                            background: #6c757d; 
                            color: white; 
                            border: none; 
                            padding: 0.75rem 1.25rem; 
                            border-radius: 6px; 
                            text-decoration: none; 
                            font-size: 1rem; 
                            font-weight: 500;
                            transition: all 0.3s ease;
                            display: flex; 
                            align-items: center; 
                            gap: 0.5rem;">
                            <svg style="width: 16px; height: 16px;" fill="white" viewBox="0 0 24 24">
                                <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                            </svg>
                            Clear
                        </a>
                    @endif
                </form>
            </div>
            @if(request('search'))
                <div style="margin-top: 1rem; padding: 0.75rem; background: rgba(193, 154, 97, 0.1); border-radius: 6px; text-align: center;">
                    <span style="color: #C19A61; font-weight: 500;">Showing results for: <strong>"{{ request('search') }}"</strong></span>
                </div>
            @endif
        </div>

        <style>
            .search-btn:hover {
                background: #218838 !important;
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3) !important;
            }
            
            .clear-btn:hover {
                background: #5a6268 !important;
                transform: translateY(-1px);
            }
            
            .form-control:focus {
                border-color: #28a745 !important;
                box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
            }
        </style>

        <!-- Membership Status Legend -->
        <div style="display: flex; gap: 1rem; margin-bottom: 1rem; padding: 0.75rem; background: #f8f9fa; border-radius: 6px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 20px; height: 20px; background: #28a745; border-radius: 4px;"></div>
                <span style="font-size: 0.85rem; color: #495057;"><strong>Active Membership (>30 days)</strong></span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 20px; height: 20px; background: #ff6c37; border-radius: 4px;"></div>
                <span style="font-size: 0.85rem; color: #495057;"><strong>Expiring Soon (≤30 days)</strong></span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 20px; height: 20px; background: #dc3545; border-radius: 4px;"></div>
                <span style="font-size: 0.85rem; color: #495057;"><strong>Expired Membership / Removed from Dashboard</strong></span>
            </div>
        </div>

        @if($users->count() > 0)
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Email Verified</th>
                            <th>Payments</th>
                            <th>Registered</th>
                            <th>Actions</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr style="{{ $user->membership_status ? 'border-left: 5px solid ' . $user->membership_status['border_color'] . ';' : '' }}">
                                <td>{{ $user->id }}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span>{{ $user->name }}</span>
                                        @if ($user->membership_status)
                                            @php $badge = $user->membership_status['status_badge']; @endphp
                                            <span style="background: {{ $badge['background'] }}; color: {{ $badge['color'] }}; padding: 0.15rem 0.3rem; border-radius: 3px; font-size: 0.65rem; font-weight: bold;">
                                                {{ $badge['text'] }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if(auth()->user()->isSuperAdmin())
                                        <form method="POST" action="{{ route('admin.users.update-role', $user) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" onchange="this.form.submit()" 
                                                    style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; border: 1px solid #ddd;"
                                                    {{ $user->isSuperAdmin() && auth()->user()->id === $user->id ? 'disabled' : '' }}>
                                                @foreach(\App\Models\User::getRoles() as $role => $label)
                                                    <option value="{{ $role }}" {{ $user->role === $role ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @else
                                        <span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;
                                              background-color: {{ $user->isSuperAdmin() ? '#dc3545' : ($user->isAdmin() ? '#d4edda' : '#e9ecef') }};
                                              color: {{ $user->isSuperAdmin() ? 'white' : ($user->isAdmin() ? '#155724' : '#495057') }};">
                                            {{ \App\Models\User::getRoles()[$user->role] }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->hasVerifiedEmail())
                                        <span style="color: #28a745;">✓ Verified</span>
                                    @else
                                        <span style="color: #dc3545;">✗ Not verified</span>
                                    @endif
                                </td>
                                <td>{{ $user->payments->count() }}</td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                        @if(auth()->user()->isSuperAdmin() && $user->payments->count() > 0)
                                            <a href="{{ route('admin.exports.admin.form', $user) }}" 
                                               style="background: #1F6E38; color: white; border: none; padding: 0.6rem 0.8rem; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;"
                                               title="Export Payment History (PDF)">
                                                <svg style="width: 18px; height: 18px;" fill="white" viewBox="0 0 24 24">
                                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M9.5,12.5A1.5,1.5 0 0,1 11,11H13A1.5,1.5 0 0,1 14.5,12.5V13.5A1.5,1.5 0 0,1 13,15H12V16.5H10.5V11H9.5V12.5M12,12.5V13.5H13V12.5H12M15.5,11H17V16.5H15.5V14.5H14.5V13H15.5V11Z"/>
                                                </svg>
                                            </a>
                                        @endif
                                        
                                        @if(auth()->user()->isSuperAdmin() && !$user->isSuperAdmin())
                                            <form method="POST" action="{{ route('admin.users.delete', $user) }}" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to permanently delete this user and all their data? This action cannot be undone!')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        style="background: #dc3545; color: white; border: none; padding: 0.6rem 0.8rem; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;"
                                                        title="Delete User and All Data (GDPR)">
                                                    <svg style="width: 18px; height: 18px;" fill="white" viewBox="0 0 24 24">
                                                        <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                {{ $users->links() }}
            </div>
        @else
            <p>No users found.</p>
        @endif
    </div>

    <div class="card">
        <h2 class="card-title">GDPR Compliance</h2>
        <p>This system provides GDPR compliance features:</p>
        <ul>
            <li><strong>Export Data:</strong> Users can request their data by clicking the "Export" button, which downloads a JSON file containing all their information.</li>
            <li><strong>Delete Account:</strong> Users can be permanently deleted along with all their associated data (payments, etc.).</li>
            <li><strong>Data Retention:</strong> All user data is stored securely and only accessible by administrators.</li>
        </ul>
    </div>
</x-app-layout> 