<x-app-layout>
    <div class="card">
        <h1 class="card-title">
            Paneli i Administratorit
            <span style="font-size: 0.8rem; color: #666; font-weight: normal;">
                ({{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Admin' }})
            </span>
        </h1>
        <p>Mirë se u kthyet, <strong>{{ auth()->user()->name }}</strong>! Ky është përmbledhja e komunitetit tuaj.</p>
    </div>

    <!-- Admin Quick Actions -->
    <div class="card">
        <h2 class="card-title">Veprime të Shpejta</h2>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <!-- System Backup -->
            <button onclick="showBackupModal();" style="background: #1F6E38; color: white; padding: 1rem 1.5rem; border-radius: 12px; border: none; text-align: center; transition: all 0.3s ease; cursor: pointer; width: 100%;" 
                    onmouseover="this.style.background='#0d4d1f'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(31, 110, 56, 0.5)'" 
                    onmouseout="this.style.background='#1F6E38'; this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                    id="backup-btn">
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem;">
                    <svg style="width: 32px; height: 32px;" fill="white" viewBox="0 0 24 24">
                        <path d="M12,1L8,5H11V14H13V5H16M18,23H6C4.89,23 4,22.1 4,21V9A2,2 0 0,1 6,7H9V9H6V21H18V9H15V7H18A2,2 0 0,1 20,9V21A2,2 0 0,1 18,23Z"/>
                    </svg>
                    <div style="font-weight: bold; font-size: 0.95rem;">Kopje Rezervë e Sistemit</div>
                    <div style="font-size: 0.75rem; opacity: 0.9;">Krijo kopje rezervë</div>
                </div>
            </button>

            <!-- Clear Logs -->
            <button onclick="showClearLogsModal();" style="background: #1F6E38; color: white; padding: 1rem 1.5rem; border-radius: 12px; border: none; text-align: center; transition: all 0.3s ease; cursor: pointer; width: 100%;" 
                    onmouseover="this.style.background='#0d4d1f'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(31, 110, 56, 0.5)'" 
                    onmouseout="this.style.background='#1F6E38'; this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                    id="clear-logs-btn">
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem;">
                    <svg style="width: 32px; height: 32px;" fill="white" viewBox="0 0 24 24">
                        <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"/>
                    </svg>
                    <div style="font-weight: bold; font-size: 0.95rem;">Pastro Regjistrimet</div>
                    <div style="font-size: 0.75rem; opacity: 0.9;">Pastro regjistrimet e sistemit</div>
                </div>
            </button>

            <!-- Testing Dashboard -->
            <button onclick="window.location.href='/testing-dashboard';" style="background: #1F6E38; color: white; padding: 1rem 1.5rem; border-radius: 12px; border: none; text-align: center; transition: all 0.3s ease; cursor: pointer; width: 100%;" 
                    onmouseover="this.style.background='#0d4d1f'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(31, 110, 56, 0.5)'" 
                    onmouseout="this.style.background='#1F6E38'; this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                    id="testing-btn">
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.5rem;">
                    <svg style="width: 32px; height: 32px;" fill="white" viewBox="0 0 24 24">
                        <path d="M7,2V4H8V18A4,4 0 0,0 12,22A4,4 0 0,0 16,18V4H17V2H7M11,16C10.4,16 10,15.6 10,15C10,14.4 10.4,14 11,14C11.6,14 12,14.4 12,15C12,15.6 11.6,16 11,16M13,12C12.4,12 12,11.6 12,11C12,10.4 12.4,10 13,10C13.6,10 14,10.4 14,11C14,11.6 13.6,12 13,12M14,7H10V9H14V7Z"/>
                    </svg>
                    <div style="font-weight: bold; font-size: 0.95rem;">Paneli i Testimit</div>
                    <div style="font-size: 0.75rem; opacity: 0.9;">Zbato testet e sistemit</div>
                </div>
            </button>
        </div>
    </div>

    <!-- Community Statistics -->
    <div class="card">
        <h2 class="card-title">Përmbledhje e Komunitetit</h2>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1.5rem;">
            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Gjithsej Anëtarë</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1F6E38;">
                    {{ $stats['total_users'] }}
                </div>
                <small style="color: #666;">Anëtarë të regjistruar</small>
            </div>

            <div style="background: rgba(193, 154, 97, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Anëtarë Aktivë</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #C19A61;">
                    {{ $stats['membership_payments'] }}
                </div>
                <small style="color: #666;">Anëtarësi CHF 350</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Të Ardhurat Totale</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1F6E38;">
                    CHF {{ number_format($stats['total_revenue'], 2) }}
                </div>
                <small style="color: #666;">Të gjitha pagesat e përfunduara</small>
            </div>

            <div style="background: rgba(193, 154, 97, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Dhurimet Totale</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #C19A61;">
                    CHF {{ number_format($stats['total_donations'], 2) }}
                </div>
                <small style="color: #666;">Kontributet e dhurimit</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Anëtarë të Rinj</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1F6E38;">
                    {{ $stats['recent_registrations'] }}
                </div>
                <small style="color: #666;">30 ditët e fundit</small>
            </div>

            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1.5rem; text-align: center;">
                <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;">Në Pritje</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #dc3545;">
                    {{ $stats['pending_payments'] }}
                </div>
                <small style="color: #666;">Në pritje të pagesës</small>
            </div>
        </div>
    </div>

    <!-- Membership Renewals (Only for Super Admins) -->
    @if(auth()->user()->isSuperAdmin() && $renewals)
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="card-title" style="margin: 0;">Njoftimet për Rinovimin e Anëtarësisë</h2>
            
            <!-- NOTIFY ALL EXPIRED USERS Button -->
            @if($renewals['stats']['expired'] > 0)
            <button onclick="showNotifyAllExpiredModal();" 
                    style="background: #dc3545; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; border: none; font-weight: bold; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);" 
                    onmouseover="this.style.background='#c82333'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(220, 53, 69, 0.4)'" 
                    onmouseout="this.style.background='#dc3545'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(220, 53, 69, 0.3)'"
                    id="notify-all-expired-btn">
                🚨 NJOFTO TË GJITHË ANËTARËT E SKADUAR ({{ $renewals['stats']['expired'] }})
            </button>
            @endif
        </div>
        
        <!-- Renewal Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #1F6E38;">{{ $renewals['stats']['total_active_memberships'] }}</div>
                <div style="font-size: 0.9rem; color: #666;">Anëtarësi Aktive</div>
            </div>
            <div style="background: rgba(255, 193, 7, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #ffc107;">{{ $renewals['stats']['expiring_within_30_days'] }}</div>
                <div style="font-size: 0.9rem; color: #666;">Skadon (30 ditë)</div>
            </div>
            <div style="background: rgba(255, 108, 55, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #ff6c37;">{{ $renewals['stats']['expiring_within_7_days'] }}</div>
                <div style="font-size: 0.9rem; color: #666;">Urgjent (7 ditë)</div>
            </div>
            <div style="background: rgba(220, 53, 69, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #dc3545;">{{ $renewals['stats']['expired'] }}</div>
                <div style="font-size: 0.9rem; color: #666;">Ka skaduar</div>
            </div>
        </div>

        <!-- Renewals List -->
        @if($renewals['renewals']->count() > 0)
            @php
                $renewalsCollection = collect($renewals['renewals']);
                $currentPage = request()->get('renewal_page', 1);
                $perPage = 6;
                $renewalsPaginated = $renewalsCollection->forPage($currentPage, $perPage);
                $totalPages = ceil($renewalsCollection->count() / $perPage);
            @endphp
            
            <!-- Pagination Info -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 0.5rem 0; border-bottom: 1px solid #e9ecef;">
                <div style="font-size: 0.9rem; color: #666;">
                    Showing {{ ($currentPage - 1) * $perPage + 1 }}-{{ min($currentPage * $perPage, $renewalsCollection->count()) }} of {{ $renewalsCollection->count() }} renewals
                </div>
                
                @if($totalPages > 1)
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    @if($currentPage > 1)
                        <a href="?renewal_page={{ $currentPage - 1 }}" style="padding: 0.25rem 0.5rem; background: #1F6E38; color: white; text-decoration: none; border-radius: 4px; font-size: 0.8rem;">← Prev</a>
                    @endif
                    
                    <span style="font-size: 0.9rem; color: #666;">Page {{ $currentPage }} of {{ $totalPages }}</span>
                    
                    @if($currentPage < $totalPages)
                        <a href="?renewal_page={{ $currentPage + 1 }}" style="padding: 0.25rem 0.5rem; background: #1F6E38; color: white; text-decoration: none; border-radius: 4px; font-size: 0.8rem;">Next →</a>
                    @endif
                </div>
                @endif
            </div>
            
            <!-- Compact Grid Layout - 3x2 Format -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1rem;">
                @foreach($renewalsPaginated as $renewal)
                    @php
                        // Use calculated days instead of stored database field
                        $calculatedDays = $renewal->calculateDaysUntilExpiry();
                        
                        // New Logic: Orange for 30 days, Red for 7 days or expired
                        if ($calculatedDays <= 0) {
                            // EXPIRED - RED
                            $colors = [
                                'bg' => 'rgba(220, 53, 69, 0.1)',
                                'border' => '#dc3545',
                                'text' => '#721c24'
                            ];
                        } elseif ($calculatedDays <= 7) {
                            // CRITICAL (7 days or less) - RED  
                            $colors = [
                                'bg' => 'rgba(220, 53, 69, 0.1)',
                                'border' => '#dc3545',
                                'text' => '#721c24'
                            ];
                        } elseif ($calculatedDays <= 30) {
                            // WARNING (30 days or less) - ORANGE
                            $colors = [
                                'bg' => 'rgba(255, 108, 55, 0.1)',
                                'border' => '#ff6c37',
                                'text' => '#8a4a00'
                            ];
                        } else {
                            // Should not appear in dashboard (30+ days)
                            $colors = [
                                'bg' => 'transparent',
                                'border' => '#e9ecef',
                                'text' => '#495057'
                            ];
                        }
                    @endphp
                    
                    <!-- Compact Card -->
                    <div style="background: {{ $colors['bg'] }}; border: 1px solid {{ $colors['border'] }}; border-radius: 8px; padding: 1rem; position: relative; min-height: 180px; display: flex; flex-direction: column;">
                        <!-- Header with Name and Status -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; color: {{ $colors['text'] }}; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $renewal->user->name }}
                                </h4>
                                <div style="font-size: 0.75rem; color: #666; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $renewal->user->email }}</div>
                            </div>
                            
                            <div style="text-align: right; flex-shrink: 0; margin-left: 0.5rem;">
                                @if($calculatedDays <= 0)
                                    <span style="background: #dc3545; color: white; padding: 0.15rem 0.3rem; border-radius: 3px; font-size: 0.65rem; font-weight: bold;">
                                        EXPIRED
                                    </span>
                                @elseif($calculatedDays <= 7)
                                    <span style="background: #dc3545; color: white; padding: 0.15rem 0.3rem; border-radius: 3px; font-size: 0.65rem; font-weight: bold;">
                                        {{ $calculatedDays }}D
                                    </span>
                                @else
                                    <span style="background: #ff6c37; color: white; padding: 0.15rem 0.3rem; border-radius: 3px; font-size: 0.65rem; font-weight: bold;">
                                        {{ $calculatedDays }}D
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Message -->
                        <div style="font-size: 0.8rem; color: {{ $colors['text'] }}; margin-bottom: 0.75rem; line-height: 1.3; flex: 1; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                            {{ $renewal->getNotificationMessage() }}
                        </div>
                        
                        <!-- Membership Info -->
                        <div style="font-size: 0.7rem; color: #666; margin-bottom: 0.75rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                <span><strong>Start:</strong> {{ $renewal->membership_start_date->format('M d') }}</span>
                                <span><strong>End:</strong> {{ $renewal->membership_end_date->format('M d') }}</span>
                            </div>
                            @if($renewal->notifications_sent && count($renewal->notifications_sent) > 0)
                                <div style="color: #28a745; font-size: 0.65rem;">
                                    ✓ Notified: {{ implode(', ', array_map(fn($days) => $days . 'd', $renewal->notifications_sent)) }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Action Buttons -->
                        <div style="display: flex; gap: 0.4rem; justify-content: space-between; margin-top: auto;">
                            <button onclick="sendRenewalNotification({{ $renewal->id }})" 
                                    style="background: #1F6E38; color: white; border: none; padding: 0.4rem 0.5rem; border-radius: 4px; cursor: pointer; font-weight: normal; font-size: 0.7rem; white-space: nowrap; flex: 1; transition: all 0.2s ease; min-height: 32px;"
                                    onmouseover="this.style.background='#28a745'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)'"
                                    onmouseout="this.style.background='#1F6E38'; this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                                    id="notify-btn-{{ $renewal->id }}">
                                Send
                            </button>
                            
                            <button onclick="deleteRenewal({{ $renewal->id }})" 
                                    style="background: #dc3545; color: white; border: none; padding: 0.4rem 0.5rem; border-radius: 4px; cursor: pointer; font-weight: normal; font-size: 0.7rem; white-space: nowrap; flex: 1; transition: all 0.2s ease; min-height: 32px;"
                                    onmouseover="this.style.background='#c82333'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)'"
                                    onmouseout="this.style.background='#dc3545'; this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                                    title="Remove from renewal notifications">
                                Delete
                            </button>
                        </div>
                    </div>
                @endforeach
                
                <!-- Fill empty slots if less than 6 items -->
                @for($i = $renewalsPaginated->count(); $i < 6; $i++)
                    <div style="background: rgba(248, 249, 250, 0.5); border: 1px dashed #dee2e6; border-radius: 8px; min-height: 180px; display: flex; align-items: center; justify-content: center;">
                        <span style="color: #6c757d; font-size: 0.8rem;">Empty</span>
                    </div>
                @endfor
            </div>
            
            <!-- Bottom Pagination -->
            @if($totalPages > 1)
            <div style="display: flex; justify-content: center; align-items: center; margin-top: 1.5rem; gap: 0.5rem;">
                @if($currentPage > 1)
                    <a href="?renewal_page=1" style="padding: 0.4rem 0.8rem; background: #f8f9fa; color: #495057; text-decoration: none; border-radius: 4px; font-size: 0.85rem; border: 1px solid #dee2e6;">First</a>
                    <a href="?renewal_page={{ $currentPage - 1 }}" style="padding: 0.4rem 0.8rem; background: #1F6E38; color: white; text-decoration: none; border-radius: 4px; font-size: 0.85rem;">← Previous</a>
                @endif
                
                <span style="padding: 0.4rem 0.8rem; background: #1F6E38; color: white; border-radius: 4px; font-size: 0.85rem;">
                    {{ $currentPage }} / {{ $totalPages }}
                </span>
                
                @if($currentPage < $totalPages)
                    <a href="?renewal_page={{ $currentPage + 1 }}" style="padding: 0.4rem 0.8rem; background: #1F6E38; color: white; text-decoration: none; border-radius: 4px; font-size: 0.85rem;">Next →</a>
                    <a href="?renewal_page={{ $totalPages }}" style="padding: 0.4rem 0.8rem; background: #f8f9fa; color: #495057; text-decoration: none; border-radius: 4px; font-size: 0.85rem; border: 1px solid #dee2e6;">Last</a>
                @endif
            </div>
            @endif
        @else
            <div style="text-align: center; padding: 2rem; color: #666; background: rgba(31, 110, 56, 0.05); border-radius: 8px;">
                <h3 style="margin-bottom: 1rem;">All Clear!</h3>
                <p>No membership renewals require immediate attention at this time.</p>
            </div>
        @endif
    </div>
    @endif

    <!-- Professional Confirmation Modals -->
    <!-- System Backup Modal -->
    <div id="backupModal" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(17, 24, 39, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    ">
        <div style="
            background: white;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 28rem;
            width: 100%;
            margin: 1rem;
            transform: scale(0.95);
            transition: transform 0.3s ease;
        ">
            <!-- Icon -->
            <div style="padding: 2rem 2rem 0 2rem; text-align: center;">
                <div style="
                    width: 64px;
                    height: 64px;
                    background: #dcfce7;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 1.5rem auto;
                ">
                    <svg style="width: 32px; height: 32px;" fill="#16a34a" viewBox="0 0 24 24">
                        <path d="M12,1L8,5H11V14H13V5H16M18,23H6C4.89,23 4,22.1 4,21V9A2,2 0 0,1 6,7H9V9H6V21H18V9H15V7H18A2,2 0 0,1 20,9V21A2,2 0 0,1 18,23Z"/>
                    </svg>
                </div>
            </div>
            
            <div style="padding: 0 2rem 2rem 2rem;">
                <h3 style="
                    font-size: 1.5rem;
                    font-weight: 700;
                    color: #111827;
                    text-align: center;
                    margin-bottom: 0.75rem;
                    margin-top: 0;
                ">
                    Create System Backup
                </h3>
                
                <p style="
                    color: #6b7280;
                    text-align: center;
                    margin-bottom: 1.5rem;
                    font-size: 0.875rem;
                    margin-top: 0;
                ">
                    Database backup confirmation
                </p>
                
                <p style="
                    color: #374151;
                    text-align: center;
                    margin-bottom: 2rem;
                    line-height: 1.6;
                    margin-top: 0;
                ">
                    This will create a complete backup of your database. The backup will be stored securely and can be used for recovery purposes.
                </p>
                
                <!-- Action Buttons -->
                <div style="
                    display: flex;
                    gap: 0.75rem;
                    flex-direction: column;
                ">
                    <button type="button" onclick="closeBackupModal()" style="
                        flex: 1;
                        padding: 0.75rem 1.5rem;
                        background: white;
                        border: 2px solid #d1d5db;
                        color: #374151;
                        border-radius: 8px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        font-size: 1rem;
                    " onmouseover="
                        this.style.background='#f9fafb';
                        this.style.borderColor='#9ca3af';
                        this.style.transform='translateY(-1px)';
                    " onmouseout="
                        this.style.background='white';
                        this.style.borderColor='#d1d5db';
                        this.style.transform='translateY(0)';
                    ">
                        Cancel
                    </button>
                    <button type="button" onclick="confirmBackup()" style="
                        flex: 1;
                        padding: 0.75rem 1.5rem;
                        background: #16a34a;
                        color: white;
                        border-radius: 8px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                        border: none;
                        font-size: 1rem;
                    " onmouseover="
                        this.style.background='#15803d';
                        this.style.transform='translateY(-1px)';
                        this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.1)';
                    " onmouseout="
                        this.style.background='#16a34a';
                        this.style.transform='translateY(0)';
                        this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';
                    ">
                        Create Backup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Clear Logs Modal -->
    <div id="clearLogsModal" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(17, 24, 39, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    ">
        <div style="
            background: white;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 28rem;
            width: 100%;
            margin: 1rem;
            transform: scale(0.95);
            transition: transform 0.3s ease;
        ">
            <!-- Icon -->
            <div style="padding: 2rem 2rem 0 2rem; text-align: center;">
                <div style="
                    width: 64px;
                    height: 64px;
                    background: #fecaca;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 1.5rem auto;
                ">
                    <svg style="width: 32px; height: 32px;" fill="#dc2626" viewBox="0 0 24 24">
                        <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"/>
                    </svg>
                </div>
            </div>
            
            <div style="padding: 0 2rem 2rem 2rem;">
                <h3 style="
                    font-size: 1.5rem;
                    font-weight: 700;
                    color: #111827;
                    text-align: center;
                    margin-bottom: 0.75rem;
                    margin-top: 0;
                ">
                    Clear System Logs
                </h3>
                
                <p style="
                    color: #6b7280;
                    text-align: center;
                    margin-bottom: 1.5rem;
                    font-size: 0.875rem;
                    margin-top: 0;
                ">
                    Permanent deletion warning
                </p>
                
                <p style="
                    color: #374151;
                    text-align: center;
                    margin-bottom: 2rem;
                    line-height: 1.6;
                    margin-top: 0;
                ">
                    This will permanently delete all system log files to free up disk space. This action cannot be undone.
                </p>
                
                <!-- Action Buttons -->
                <div style="
                    display: flex;
                    gap: 0.75rem;
                    flex-direction: column;
                ">
                    <button type="button" onclick="closeClearLogsModal()" style="
                        flex: 1;
                        padding: 0.75rem 1.5rem;
                        background: white;
                        border: 2px solid #d1d5db;
                        color: #374151;
                        border-radius: 8px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        font-size: 1rem;
                    " onmouseover="
                        this.style.background='#f9fafb';
                        this.style.borderColor='#9ca3af';
                        this.style.transform='translateY(-1px)';
                    " onmouseout="
                        this.style.background='white';
                        this.style.borderColor='#d1d5db';
                        this.style.transform='translateY(0)';
                    ">
                        Cancel
                    </button>
                    <button type="button" onclick="confirmClearLogs()" style="
                        flex: 1;
                        padding: 0.75rem 1.5rem;
                        background: #dc2626;
                        color: white;
                        border-radius: 8px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                        border: none;
                        font-size: 1rem;
                    " onmouseover="
                        this.style.background='#b91c1c';
                        this.style.transform='translateY(-1px)';
                        this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.1)';
                    " onmouseout="
                        this.style.background='#dc2626';
                        this.style.transform='translateY(0)';
                        this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';
                    ">
                        Clear Logs
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Send Notifications Modal -->
    <div id="notificationsModal" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(17, 24, 39, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    ">
        <div style="
            background: white;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 28rem;
            width: 100%;
            margin: 1rem;
            transform: scale(0.95);
            transition: transform 0.3s ease;
        ">
            <!-- Icon -->
            <div style="padding: 2rem 2rem 0 2rem; text-align: center;">
                <div style="
                    width: 64px;
                    height: 64px;
                    background: #dbeafe;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 1.5rem auto;
                ">
                    <svg style="width: 32px; height: 32px;" fill="#2563eb" viewBox="0 0 24 24">
                        <path d="M21,19V20H3V19L5,17V11C5,7.9 7.03,5.17 10,4.29C10,4.19 10,4.1 10,4A2,2 0 0,1 12,2A2,2 0 0,1 14,4C14,4.1 14,4.19 14,4.29C16.97,5.17 19,7.9 19,11V17L21,19M14,21A2,2 0 0,1 12,23A2,2 0 0,1 10,21"/>
                    </svg>
                </div>
            </div>
            
            <div style="padding: 0 2rem 2rem 2rem;">
                <h3 style="
                    font-size: 1.5rem;
                    font-weight: 700;
                    color: #111827;
                    text-align: center;
                    margin-bottom: 0.75rem;
                    margin-top: 0;
                ">
                    Send Bulk Notifications
                </h3>
                
                <p style="
                    color: #6b7280;
                    text-align: center;
                    margin-bottom: 1.5rem;
                    font-size: 0.875rem;
                    margin-top: 0;
                ">
                    Email notification confirmation
                </p>
                
                <p style="
                    color: #374151;
                    text-align: center;
                    margin-bottom: 2rem;
                    line-height: 1.6;
                    margin-top: 0;
                ">
                    This will send renewal notifications to all users with expiring memberships. Are you sure you want to proceed?
                </p>
                
                <!-- Action Buttons -->
                <div style="
                    display: flex;
                    gap: 0.75rem;
                    flex-direction: column;
                ">
                    <button type="button" onclick="closeNotificationsModal()" style="
                        flex: 1;
                        padding: 0.75rem 1.5rem;
                        background: white;
                        border: 2px solid #d1d5db;
                        color: #374151;
                        border-radius: 8px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        font-size: 1rem;
                    " onmouseover="
                        this.style.background='#f9fafb';
                        this.style.borderColor='#9ca3af';
                        this.style.transform='translateY(-1px)';
                    " onmouseout="
                        this.style.background='white';
                        this.style.borderColor='#d1d5db';
                        this.style.transform='translateY(0)';
                    ">
                        Cancel
                    </button>
                    <button type="button" onclick="confirmSendNotifications()" style="
                        flex: 1;
                        padding: 0.75rem 1.5rem;
                        background: #2563eb;
                        color: white;
                        border-radius: 8px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                        border: none;
                        font-size: 1rem;
                    " onmouseover="
                        this.style.background='#1d4ed8';
                        this.style.transform='translateY(-1px)';
                        this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.1)';
                    " onmouseout="
                        this.style.background='#2563eb';
                        this.style.transform='translateY(0)';
                        this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';
                    ">
                        Send Notifications
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notify All Expired Users Modal -->
    <div id="notifyAllExpiredModal" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        backdrop-filter: blur(4px);
    ">
        <div style="
            background: white;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 28rem;
            width: 100%;
            margin: 1rem;
            transform: scale(0.95);
            transition: transform 0.3s ease;
        ">
            <!-- Icon -->
            <div style="padding: 2rem 2rem 0 2rem; text-align: center;">
                <div style="
                    width: 64px;
                    height: 64px;
                    background: #fee2e2;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 1.5rem auto;
                ">
                    <svg style="width: 32px; height: 32px;" fill="#dc2626" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
            </div>
            
            <!-- Content -->
            <div style="
                padding: 0 2rem 2rem 2rem;
                text-align: center;
            ">
                <h3 style="
                    font-size: 1.25rem;
                    font-weight: 600;
                    color: #1f2937;
                    margin: 0 0 1rem 0;
                ">
                    Notify All Expired Users
                </h3>
                
                <p style="
                    color: #6b7280;
                    margin-bottom: 2rem;
                    line-height: 1.6;
                    margin-top: 0;
                ">
                    This will send personalized renewal notifications to all <strong>{{ $renewals['stats']['expired'] ?? 0 }} expired users</strong>. Each user will receive a customized email with their specific membership details and renewal instructions.
                </p>
                
                <!-- Action Buttons -->
                <div style="
                    display: flex;
                    gap: 0.75rem;
                    flex-direction: column;
                ">
                    <button type="button" onclick="closeNotifyAllExpiredModal()" style="
                        flex: 1;
                        padding: 0.75rem 1.5rem;
                        background: white;
                        border: 2px solid #d1d5db;
                        color: #374151;
                        border-radius: 8px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        font-size: 1rem;
                    " onmouseover="
                        this.style.background='#f9fafb';
                        this.style.borderColor='#9ca3af';
                        this.style.transform='translateY(-1px)';
                    " onmouseout="
                        this.style.background='white';
                        this.style.borderColor='#d1d5db';
                        this.style.transform='translateY(0)';
                    ">
                        Cancel
                    </button>
                    <button type="button" onclick="confirmNotifyAllExpired()" style="
                        flex: 1;
                        padding: 0.75rem 1.5rem;
                        background: #dc2626;
                        color: white;
                        border-radius: 8px;
                        font-weight: 500;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                        border: none;
                        font-size: 1rem;
                    " onmouseover="
                        this.style.background='#b91c1c';
                        this.style.transform='translateY(-1px)';
                        this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.1)';
                    " onmouseout="
                        this.style.background='#dc2626';
                        this.style.transform='translateY(0)';
                        this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)';
                    ">
                        🚨 Send to All Expired Users
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Modal Animation */
        #backupModal.show, #clearLogsModal.show, #notificationsModal.show, #notifyAllExpiredModal.show {
            display: flex !important;
        }

        #backupModal.show > div, #clearLogsModal.show > div, #notificationsModal.show > div, #notifyAllExpiredModal.show > div {
            transform: scale(1) !important;
        }

        /* Responsive adjustments */
        @media (min-width: 640px) {
            #backupModal > div > div:last-child > div,
            #clearLogsModal > div > div:last-child > div,
            #notificationsModal > div > div:last-child > div,
            #notifyAllExpiredModal > div > div:last-child > div {
                flex-direction: row !important;
            }
        }

        /* Focus states for accessibility */
        #backupModal button:focus, #clearLogsModal button:focus, #notificationsModal button:focus, #notifyAllExpiredModal button:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
    </style>

    <script>
        // Modal Management Functions
        function showBackupModal() {
            const modal = document.getElementById('backupModal');
            modal.classList.add('show');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeBackupModal() {
            const modal = document.getElementById('backupModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        function showClearLogsModal() {
            const modal = document.getElementById('clearLogsModal');
            modal.classList.add('show');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeClearLogsModal() {
            const modal = document.getElementById('clearLogsModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        function showNotificationsModal() {
            const modal = document.getElementById('notificationsModal');
            modal.classList.add('show');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeNotificationsModal() {
            const modal = document.getElementById('notificationsModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        function showNotifyAllExpiredModal() {
            const modal = document.getElementById('notifyAllExpiredModal');
            modal.classList.add('show');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeNotifyAllExpiredModal() {
            const modal = document.getElementById('notifyAllExpiredModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        // Confirmation Functions
        function confirmBackup() {
            closeBackupModal();
            performBackup();
        }

        function confirmClearLogs() {
            closeClearLogsModal();
            clearSystemLogs();
        }

        function confirmSendNotifications() {
            closeNotificationsModal();
            sendBulkNotifications();
        }

        function confirmNotifyAllExpired() {
            closeNotifyAllExpiredModal();
            sendNotifyAllExpired();
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                color: white;
                font-weight: bold;
                z-index: 1000;
                opacity: 0;
                transition: opacity 0.3s ease;
                ${type === 'success' ? 'background: #28a745;' : 'background: #dc3545;'}
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Fade in
            setTimeout(() => notification.style.opacity = '1', 100);
            
            // Remove after 5 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => document.body.removeChild(notification), 300);
            }, 5000);
        }

        function performBackup() {
            const button = document.getElementById('backup-btn');
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = 'Creating Backup...';
            button.disabled = true;
            
            fetch('/admin/system/backup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Backup created successfully!', 'success');
                } else {
                    showNotification('Failed to create backup: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error creating backup: ' + error.message, 'error');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function clearSystemLogs() {
            const button = document.getElementById('clear-logs-btn');
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = 'Clearing Logs...';
            button.disabled = true;
            
            fetch('/admin/system/clear-logs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('System logs cleared successfully!', 'success');
                } else {
                    showNotification('Failed to clear logs: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error clearing logs: ' + error.message, 'error');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function sendBulkNotifications() {
            const button = document.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '⏳ Sending Emails...';
            button.disabled = true;
            
            fetch('/admin/notifications/bulk-send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const message = `✅ Successfully sent ${data.sent_count} personalized notifications to expired users!` + 
                                  (data.failed_count > 0 ? ` (${data.failed_count} failed)` : '');
                    showNotification(message, 'success');
                    
                    // Show detailed results
                    console.log('Bulk notification results:', data.results);
                    
                    // Update button to show success
                    button.innerHTML = `✅ Sent ${data.sent_count} Emails!`;
                    button.style.background = '#28a745';
                    
                    // Reset after 5 seconds
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.style.background = '#dc3545';
                        button.disabled = false;
                    }, 5000);
                } else {
                    showNotification('Failed to send notifications: ' + (data.message || data.error || 'Unknown error'), 'error');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error sending notifications: ' + error.message, 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function sendNotifyAllExpired() {
            const button = document.getElementById('notify-all-expired-btn');
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '⏳ Sending to All Expired Users...';
            button.disabled = true;
            
            fetch('/admin/notifications/bulk-send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    target: 'expired_only'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const message = `✅ Successfully sent ${data.sent_count} personalized notifications to expired users!` + 
                                  (data.failed_count > 0 ? ` (${data.failed_count} failed)` : '');
                    showNotification(message, 'success');
                    
                    // Show detailed results
                    console.log('Expired users notification results:', data.results);
                    
                    // Update button to show success
                    button.innerHTML = `✅ Sent ${data.sent_count} Emails!`;
                    button.style.background = '#28a745';
                    
                    // Reset after 5 seconds
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.style.background = '#dc3545';
                        button.disabled = false;
                    }, 5000);
                } else {
                    showNotification('Failed to send notifications: ' + (data.message || data.error || 'Unknown error'), 'error');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error sending notifications: ' + error.message, 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        async function sendRenewalNotification(renewalId) {
            const button = document.getElementById(`notify-btn-${renewalId}`);
            const originalText = button.innerHTML;
            
            button.innerHTML = '⏳ Sending...';
            button.disabled = true;
            
            try {
                const response = await fetch(`/admin/renewals/${renewalId}/notify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    button.innerHTML = '✅ Sent!';
                    button.style.background = '#28a745';
                    
                    // Show success message
                    showNotification('Renewal notification sent successfully!', 'success');
                    
                    // Reset button after 3 seconds
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.style.background = '#1F6E38';
                        button.disabled = false;
                    }, 3000);
                } else {
                    throw new Error(data.error || 'Failed to send notification');
                }
            } catch (error) {
                button.innerHTML = '❌ Failed';
                button.style.background = '#dc3545';
                showNotification('Failed to send notification: ' + error.message, 'error');
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.style.background = '#1F6E38';
                    button.disabled = false;
                }, 3000);
            }
        }

        async function deleteRenewal(renewalId) {
            if (!confirm('Are you sure you want to remove this renewal from the notification list?')) {
                return;
            }
            
            try {
                const response = await fetch(`/admin/renewals/${renewalId}/hide`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Renewal removed from notification list', 'success');
                    // Refresh the page to update the list
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(data.error || 'Failed to remove renewal');
                }
            } catch (error) {
                showNotification('Failed to remove renewal: ' + error.message, 'error');
            }
        }

        // Initialize modal event listeners when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Close modals when pressing ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeBackupModal();
                    closeClearLogsModal();
                    closeNotificationsModal();
                    closeNotifyAllExpiredModal();
                }
            });

            // Add click outside listeners
            document.getElementById('backupModal').addEventListener('click', function(e) {
                if (e.target === this) closeBackupModal();
            });

            document.getElementById('clearLogsModal').addEventListener('click', function(e) {
                if (e.target === this) closeClearLogsModal();
            });

            document.getElementById('notificationsModal').addEventListener('click', function(e) {
                if (e.target === this) closeNotificationsModal();
            });

            document.getElementById('notifyAllExpiredModal').addEventListener('click', function(e) {
                if (e.target === this) closeNotifyAllExpiredModal();
            });
        });
    </script>

    <!-- Pending Cash Payments Management -->
    <div class="card">
        <h2 class="card-title">
            Pending Cash Payments 
            @if($pendingCashPayments->count() > 0)
                <span style="background: #dc3545; color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: normal;">
                    {{ $pendingCashPayments->count() }} Awaiting Confirmation
                </span>
            @endif
        </h2>
        
        @if($pendingCashPayments->count() > 0)
            <p style="color: #666; margin-bottom: 1.5rem;">
                Cash payments waiting for your confirmation. Click "Confirm Payment" when you receive the cash.
            </p>
            
            <!-- Summary Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                @php
                    $totalPendingAmount = $pendingCashPayments->sum('amount') / 100;
                    $membershipCount = $pendingCashPayments->where('payment_type', 'membership')->count();
                    $donationCount = $pendingCashPayments->where('payment_type', 'donation')->count();
                    $donationAmount = $pendingCashPayments->where('payment_type', 'donation')->sum('amount') / 100;
                @endphp
                
                <div style="background: rgba(220, 53, 69, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #dc3545;">{{ $pendingCashPayments->count() }}</div>
                    <small style="color: #666;">Pending Payments</small>
                </div>
                
                <div style="background: rgba(193, 154, 97, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #C19A61;">CHF {{ number_format($totalPendingAmount, 2) }}</div>
                    <small style="color: #666;">Total Amount</small>
                </div>
                
                <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #1F6E38;">{{ $membershipCount }}</div>
                    <small style="color: #666;">Memberships</small>
                </div>
                
                <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #1F6E38;">{{ $donationCount }}</div>
                    <small style="color: #666;">Donations</small>
                </div>
            </div>
            
            <!-- Payments Table -->
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #dee2e6; font-weight: 600;">User</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #dee2e6; font-weight: 600;">Type</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #dee2e6; font-weight: 600;">Amount</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #dee2e6; font-weight: 600;">Date</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 1px solid #dee2e6; font-weight: 600;">Reference</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 1px solid #dee2e6; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingCashPayments as $payment)
                            <tr style="border-bottom: 1px solid #f1f3f4;">
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 600; color: #333;">{{ $payment->user->name }}</div>
                                    <div style="font-size: 0.85rem; color: #666;">{{ $payment->user->email }}</div>
                                </td>
                                <td style="padding: 1rem;">
                                    @if($payment->payment_type === 'membership')
                                        <span style="background: #1F6E38; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500;">
                                            Membership
                                        </span>
                                    @else
                                        <span style="background: #C19A61; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500;">
                                            Donation
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 600; color: #1F6E38; font-size: 1.1rem;">{{ $payment->formatted_amount }}</div>
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 500;">{{ $payment->created_at->format('M d, Y') }}</div>
                                    <div style="font-size: 0.85rem; color: #666;">{{ $payment->created_at->format('H:i') }}</div>
                                </td>
                                <td style="padding: 1rem;">
                                    <code style="background: #f8f9fa; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.9rem; color: #666;">
                                        CASH-{{ $payment->id }}
                                    </code>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <!-- Confirm Payment Button -->
                                        <button onclick="confirmCashPayment({{ $payment->id }}, '{{ $payment->user->name }}', '{{ $payment->formatted_amount }}')"
                                                style="background: #28a745; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; font-size: 0.85rem; font-weight: 500; transition: all 0.3s ease;"
                                                onmouseover="this.style.background='#218838'"
                                                onmouseout="this.style.background='#28a745'">
                                            Confirm
                                        </button>
                                        
                                        <!-- View Details Button -->
                                        <button onclick="showPaymentDetails({{ $payment->id }})" 
                                                style="background: #6c757d; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; font-size: 0.85rem; font-weight: 500; transition: all 0.3s ease;"
                                                onmouseover="this.style.background='#5a6268'"
                                                onmouseout="this.style.background='#6c757d'">
                                            Details
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            

        @else
            <div style="text-align: center; padding: 3rem; color: #666; background: rgba(31, 110, 56, 0.1); border-radius: 8px;">
                <h3 style="color: #1F6E38; margin-bottom: 0.5rem;">All Caught Up!</h3>
                <p>No pending cash payments require confirmation at this time.</p>
            </div>
        @endif
    </div>
    
    <!-- Cash Payment Confirmation Modal -->
    <div id="cashConfirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 8px; padding: 2rem; max-width: 500px; width: 90%; margin: 1rem;">
            <h3 style="margin: 0 0 1rem 0; color: #1F6E38;">Confirm Cash Payment</h3>
            <div id="cashConfirmDetails" style="background: #f8f9fa; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;"></div>
            
            <form id="cashConfirmForm" method="POST" style="margin-bottom: 1rem;">
                @csrf
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Notes (Optional):</label>
                    <textarea name="notes" placeholder="Add any notes about this cash payment confirmation..." 
                              style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; resize: vertical; min-height: 80px;"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="closeCashConfirmModal()" 
                            style="background: #6c757d; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" 
                            style="background: #28a745; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; cursor: pointer; font-weight: 500;">
                        Confirm Payment Received
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function confirmCashPayment(paymentId, userName, amount) {
            const modal = document.getElementById('cashConfirmModal');
            const form = document.getElementById('cashConfirmForm');
            const details = document.getElementById('cashConfirmDetails');
            
            // Update form action
            form.action = `/admin/payments/cash/confirm/${paymentId}`;
            
            // Update details
            details.innerHTML = `
                <div><strong>User:</strong> ${userName}</div>
                <div><strong>Amount:</strong> ${amount}</div>
                <div><strong>Payment ID:</strong> CASH-${paymentId}</div>
                <div style="margin-top: 0.5rem; color: #dc3545; font-weight: 500;">⚠️ Only confirm if you have physically received the cash payment!</div>
            `;
            
            // Show modal
            modal.style.display = 'flex';
        }
        
        function closeCashConfirmModal() {
            document.getElementById('cashConfirmModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('cashConfirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCashConfirmModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCashConfirmModal();
                closePaymentDetails();
            }
        });
        
        // Payment Details Functions
        function showPaymentDetails(paymentId) {
            fetch(`/admin/payments/${paymentId}/details`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('payment-details-content').innerHTML = data;
                    document.getElementById('payment-details-modal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load payment details');
                });
        }
        
        function closePaymentDetails() {
            document.getElementById('payment-details-modal').style.display = 'none';
        }

        // Payment Status Update Functions
        async function updatePaymentStatus(paymentId, status) {
            if (!confirm(`Are you sure you want to mark this payment as ${status}?`)) {
                return;
            }
            
            try {
                const response = await fetch(`/admin/payments/${paymentId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status })
                });
                
                if (response.ok) {
                    alert(`Payment successfully marked as ${status}`);
                    location.reload();
                } else {
                    alert('Failed to update payment status');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating payment status');
            }
        }
    </script>
    
    <!-- Payment Details Modal -->
    <div id="payment-details-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;" onclick="closePaymentDetails()">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 8px; padding: 0; max-width: 90%; max-height: 90%; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.3);" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div style="background: #1F6E38; color: white; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.2rem;">Payment Details</h3>
                <button onclick="closePaymentDetails()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: background 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='none'">
                    ×
                </button>
            </div>
            
            <!-- Modal Content -->
            <div style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
                <div id="payment-details-content">
                    <!-- Payment details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 