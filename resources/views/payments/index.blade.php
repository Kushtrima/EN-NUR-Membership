<x-app-layout>
@include('components.confirmation-modal')
    <div class="card">
        <h1 class="card-title">Pagesat e Mia</h1>
        <p>Shiko dhe menaxho historikun e pagesave të tua, faturat dhe detajet e transaksioneve.</p>
    </div>

    <!-- Payment Statistics -->
    <div class="card">
        <h2 class="card-title">Përmbledhja e Pagesave</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #1F6E38;">{{ $stats['total'] }}</div>
                <div style="font-size: 0.9rem; color: #666;">Gjithsej Pagesat</div>
            </div>
            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #1F6E38;">{{ $stats['completed'] }}</div>
                <div style="font-size: 0.9rem; color: #666;">E Përfunduar</div>
            </div>
            <div style="background: rgba(193, 154, 97, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #C19A61;">{{ $stats['pending'] }}</div>
                <div style="font-size: 0.9rem; color: #666;">Në Pritje</div>
            </div>
            @if($stats['failed'] > 0)
            <div style="background: rgba(220, 53, 69, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #dc3545;">{{ $stats['failed'] }}</div>
                <div style="font-size: 0.9rem; color: #666;">Dështuar</div>
            </div>
            @endif
            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #1F6E38;">CHF {{ number_format($stats['total_amount'], 2) }}</div>
                <div style="font-size: 0.9rem; color: #666;">Gjithsej i Paguar</div>
            </div>
            @if($stats['membership_payments'] > 0)
            <div style="background: rgba(193, 154, 97, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #C19A61;">{{ $stats['membership_payments'] }}</div>
                <div style="font-size: 0.9rem; color: #666;">Anëtarësi</div>
            </div>
            @endif
            @if($stats['donation_total'] > 0)
            <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #1F6E38;">CHF {{ number_format($stats['donation_total'], 2) }}</div>
                <div style="font-size: 0.9rem; color: #666;">Dhurime</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <h2 class="card-title">Mundësitë e Eksportimit</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <!-- Quick Export Current View -->
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Eksporto Pamjen Aktuale</h4>
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">Eksporto pagesat e shfaqura aktualisht me filtrat e aplikuar.</p>
                <form method="POST" action="{{ route('exports.user') }}" style="display: inline;">
                    @csrf
                    <!-- Pass current filters as hidden inputs -->
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    @if(request('date_from'))
                        <input type="hidden" name="start_date" value="{{ request('date_from') }}">
                    @endif
                    @if(request('date_to'))
                        <input type="hidden" name="end_date" value="{{ request('date_to') }}">
                    @endif
                    <button type="submit" style="background: #1F6E38; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;"
                            onmouseover="this.style.background='#165029'" onmouseout="this.style.background='#1F6E38'">
                        <svg style="width: 16px; height: 16px;" fill="white" viewBox="0 0 24 24">
                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                        </svg>
                        Eksporto Rezultatet e Filtruar ({{ $payments->total() }} pagesat)
                    </button>
                </form>
            </div>
            
            <!-- Advanced Export Options -->
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Eksportim i Avancuar</h4>
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">Zgjidh intervale datash dhe filtra specifikë për eksportimin tënd.</p>
                <a href="{{ route('exports.user.form') }}" 
                   style="background: #C19A61; color: white; padding: 0.75rem 1.5rem; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;"
                   onmouseover="this.style.background='#a67c52'" onmouseout="this.style.background='#C19A61'">
                    <svg style="width: 16px; height: 16px;" fill="white" viewBox="0 0 24 24">
                        <path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.22,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.22,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.68 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"/>
                    </svg>
                    Opsionet e Eksportimit të Personalizuar
                </a>
            </div>
            
            <!-- Export Summary -->
            <div>
                <h4 style="color: #1F6E38; margin-bottom: 0.5rem;">Përmbledhja e Pagesave Tuaja</h4>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; border-left: 4px solid #1F6E38;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.9rem;">
                        <div><strong>Gjithsej Pagesat:</strong> {{ $stats['total'] }}</div>
                        <div><strong>E Përfunduar:</strong> {{ $stats['completed'] }}</div>
                        <div><strong>Shuma Totale:</strong> CHF {{ number_format($stats['total_amount'], 2) }}</div>
                        <div><strong>Anëtarësi:</strong> {{ $stats['membership_payments'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card">
        <h2 class="card-title">Filtrimi & Kërkimi</h2>
        <form method="GET" action="{{ route('payment.index') }}">
            <!-- Search Bar - First Row -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #1F6E38;">Kërko</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ID e transaksionit, lloji, metoda..." 
                       style="width: 100%; padding: 0.75rem; border: 2px solid #ddd; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s ease;"
                       onfocus="this.style.borderColor='#1F6E38'" onblur="this.style.borderColor='#ddd'">
            </div>
            
            <!-- All Filters and Buttons in One Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr auto auto; gap: 1rem; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #1F6E38; font-size: 0.9rem;">Statusi</label>
                    <select name="status" style="width: 100%; padding: 0.6rem; border: 2px solid #ddd; border-radius: 8px; font-size: 0.9rem;">
                        <option value="">Të Gjitha Statuset</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>E Përfunduar</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Në Pritje</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Dështuar</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>E Anuluar</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #1F6E38; font-size: 0.9rem;">Lloji</label>
                    <select name="type" style="width: 100%; padding: 0.6rem; border: 2px solid #ddd; border-radius: 8px; font-size: 0.9rem;">
                        <option value="">Të Gjitha Llojet</option>
                        <option value="membership" {{ request('type') === 'membership' ? 'selected' : '' }}>Anëtarësi</option>
                        <option value="donation" {{ request('type') === 'donation' ? 'selected' : '' }}>Dhurim</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #1F6E38; font-size: 0.9rem;">Metoda</label>
                    <select name="method" style="width: 100%; padding: 0.6rem; border: 2px solid #ddd; border-radius: 8px; font-size: 0.9rem;">
                        <option value="">Të Gjitha Metodat</option>
                        <option value="stripe" {{ request('method') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                        <option value="paypal" {{ request('method') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                        <option value="twint" {{ request('method') === 'twint' ? 'selected' : '' }}>TWINT</option>
                        <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Transfer Bankar</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #1F6E38; font-size: 0.9rem;">Nga Data</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           style="width: 100%; padding: 0.6rem; border: 2px solid #ddd; border-radius: 8px; font-size: 0.9rem;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #1F6E38; font-size: 0.9rem;">Deri në Datë</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           style="width: 100%; padding: 0.6rem; border: 2px solid #ddd; border-radius: 8px; font-size: 0.9rem;">
                </div>
                
                <div>
                    <button type="submit" style="background: #1F6E38; color: white; padding: 0.6rem 1.5rem; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 0.9rem; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.4rem; white-space: nowrap; height: 44px; box-sizing: border-box;"
                            onmouseover="this.style.background='#165029'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='#1F6E38'; this.style.transform='translateY(0)'">
                        <svg style="width: 16px; height: 16px;" fill="white" viewBox="0 0 24 24">
                            <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/>
                        </svg>
                        Filtro
                    </button>
                </div>
                
                <div>
                    <a href="{{ route('payment.index') }}" style="background: #6c757d; color: white; padding: 0.6rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 0.9rem; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.4rem; white-space: nowrap; height: 44px; box-sizing: border-box;"
                       onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)'">
                        <svg style="width: 16px; height: 16px;" fill="white" viewBox="0 0 24 24">
                            <path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6M12,8A4,4 0 0,0 8,12A4,4 0 0,0 12,16A4,4 0 0,0 16,12A4,4 0 0,0 12,8Z"/>
                        </svg>
                        Pastro
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Payments List -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="card-title" style="margin: 0;">Historiku i Pagesave ({{ $payments->total() }} pagesat)</h2>
            @if($payments->count() > 0)
                <div style="display: flex; gap: 1rem;">
                    <!-- Export All Payments Button -->
                    <form method="POST" action="{{ route('exports.user') }}" style="display: inline;">
                        @csrf
                        <!-- Include all current filters -->
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        @if(request('type'))
                            <input type="hidden" name="type" value="{{ request('type') }}">
                        @endif
                        @if(request('date_from'))
                            <input type="hidden" name="start_date" value="{{ request('date_from') }}">
                        @endif
                        @if(request('date_to'))
                            <input type="hidden" name="end_date" value="{{ request('date_to') }}">
                        @endif
                        <button type="submit" style="background: #C19A61; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;"
                                onmouseover="this.style.background='#a67c52'" onmouseout="this.style.background='#C19A61'">
                            <svg style="width: 18px; height: 18px;" fill="white" viewBox="0 0 24 24">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                            </svg>
                            Eksporto Të Gjitha Pagesat ({{ $payments->total() }})
                        </button>
                    </form>
                    
                    <!-- Advanced Export Options -->
                    <a href="{{ route('exports.user.form') }}" 
                       style="background: #6c757d; color: white; padding: 0.75rem 1.5rem; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;"
                       onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">
                        <svg style="width: 18px; height: 18px;" fill="white" viewBox="0 0 24 24">
                            <path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.22,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.22,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.68 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"/>
                        </svg>
                        Eksportim i Personalizuar
                    </a>
                </div>
            @endif
        </div>

        @if($payments->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Data</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Lloji</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Shuma</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Metoda</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Statusi</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">ID e Transaksionit</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #1F6E38; font-weight: bold;">Veprimet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr style="border-bottom: 1px solid #e9ecef;">
                                <td style="padding: 1rem;">
                                    <div style="font-weight: bold;">{{ $payment->created_at->format('M d, Y') }}</div>
                                    <div style="font-size: 0.8rem; color: #666;">{{ $payment->created_at->format('H:i') }}</div>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="background: {{ $payment->payment_type === 'membership' ? '#1F6E38' : '#C19A61' }}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">
                                        @if($payment->payment_type === 'membership')
                                            Anëtarësi
                                        @else
                                            Dhurim
                                        @endif
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="font-weight: bold; font-size: 1.1rem;">CHF {{ number_format($payment->amount / 100, 2) }}</div>
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="text-transform: capitalize;">
                                        @if($payment->payment_method === 'bank_transfer')
                                            Transfer Bankar
                                        @else
                                            {{ str_replace('_', ' ', $payment->payment_method) }}
                                        @endif
                                    </div>
                                </td>
                                <td style="padding: 1rem;">
                                    @php
                                        $statusColors = [
                                            'completed' => '#28a745',
                                            'pending' => '#ffc107',
                                            'failed' => '#dc3545',
                                            'cancelled' => '#6c757d'
                                        ];
                                        $statusColor = $statusColors[$payment->status] ?? '#6c757d';
                                    @endphp
                                    <span style="background: {{ $statusColor }}; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">
                                        @if($payment->status === 'completed')
                                            E Përfunduar
                                        @elseif($payment->status === 'pending')
                                            Në Pritje
                                        @elseif($payment->status === 'failed')
                                            Dështuar
                                        @elseif($payment->status === 'cancelled')
                                            E Anuluar
                                        @else
                                            {{ ucfirst($payment->status) }}
                                        @endif
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="font-family: monospace; font-size: 0.9rem;">
                                        {{ $payment->transaction_id ?? 'N/A' }}
                                    </div>
                                </td>
                                <td style="padding: 1rem;">
                                    <div style="display: flex; gap: 0.5rem; align-items: center; justify-content: flex-end; flex-wrap: wrap;">
                                        @if($payment->status === 'completed')
                                            <!-- Individual PDF Receipt -->
                                            <a href="{{ route('user.payments.receipt', $payment) }}" 
                                               style="background: #1F6E38; color: white; padding: 0.5rem 0.75rem; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; display: flex; align-items: center; gap: 0.4rem; height: 32px; box-sizing: border-box; white-space: nowrap;"
                                               target="_blank"
                                               title="Shkarko faturë PDF për këtë pagesë">
                                                <svg style="width: 14px; height: 14px;" fill="white" viewBox="0 0 24 24">
                                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                                </svg>
                                                Fatura PDF
                                            </a>
                                            
                                            <!-- Quick Export This Payment -->
                                            <form method="POST" action="{{ route('exports.user') }}" style="display: inline; margin: 0;">
                                                @csrf
                                                <!-- Export only this specific payment by date -->
                                                <input type="hidden" name="start_date" value="{{ $payment->created_at->format('Y-m-d') }}">
                                                <input type="hidden" name="end_date" value="{{ $payment->created_at->format('Y-m-d') }}">
                                                <input type="hidden" name="type" value="{{ $payment->payment_type }}">
                                                <button type="submit" 
                                                        style="background: #C19A61; color: white; padding: 0.5rem 0.75rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; font-weight: bold; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.4rem; height: 32px; box-sizing: border-box; white-space: nowrap;"
                                                        onmouseover="this.style.background='#a67c52'" 
                                                        onmouseout="this.style.background='#C19A61'"
                                                        title="Eksporto këtë pagesë në raport PDF">
                                                    <svg style="width: 14px; height: 14px;" fill="white" viewBox="0 0 24 24">
                                                        <path d="M5,20H19V18H5M19,9H15V3H9V9H5L12,16L19,9Z"/>
                                                    </svg>
                                                    Eksporto PDF
                                                </button>
                                            </form>
                                        @else
                                            <span style="color: #6c757d; font-size: 0.8rem; font-style: italic;">
                                                @if($payment->status === 'pending')
                                                    Duke u procesuar...
                                                @elseif($payment->status === 'failed')
                                                    Dështuar
                                                @elseif($payment->status === 'cancelled')
                                                    E Anuluar
                                                @else
                                                    {{ ucfirst($payment->status) }}
                                                @endif
                                            </span>
                                        @endif
                                        
                                        <!-- Delete Button (smaller and less prominent) -->
                                        <form method="POST" action="{{ route('payments.delete', $payment) }}" 
                                              style="display: inline-block; margin: 0;"
                                              onsubmit="return handlePaymentDelete(event)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    style="background: #dc3545; color: white; padding: 0.4rem 0.6rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.75rem; font-weight: bold; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.3rem; height: 28px; box-sizing: border-box;"
                                                    onmouseover="this.style.background='#c82333'" 
                                                    onmouseout="this.style.background='#dc3545'"
                                                    title="Fshij këtë pagesë">
                                                <svg style="width: 12px; height: 12px;" fill="white" viewBox="0 0 24 24">
                                                    <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z"/>
                                                </svg>
                                                Fshij
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div style="margin-top: 2rem;">
                {{ $payments->withQueryString()->links() }}
            </div>
        @else
            <div style="text-align: center; padding: 3rem; color: #666; background: rgba(31, 110, 56, 0.05); border-radius: 8px;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                <h3 style="margin-bottom: 1rem;">Nuk u Gjetën Pagesa</h3>
                @if(request()->hasAny(['search', 'status', 'type', 'method', 'date_from', 'date_to']))
                    <p style="margin-bottom: 1.5rem;">Asnjë pagesë nuk përputhet me filtrat aktuale. Provo të ndryshosh kriteret e kërkimit.</p>
                    <a href="{{ route('payment.index') }}" style="background: #1F6E38; color: white; padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; font-weight: bold;">
                        Pastro Filtrat
                    </a>
                @else
                    <p style="margin-bottom: 1.5rem;">Ende nuk ke bërë asnjë pagesë. Fillo me pagesën e parë!</p>
                    <a href="{{ route('payment.create') }}" style="background: #1F6E38; color: white; padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; font-weight: bold;">
                        Bëj Pagesën e Parë
                    </a>
                @endif
            </div>
        @endif
    </div>

<script>
async function handlePaymentDelete(event) {
    event.preventDefault();
    
    const confirmed = await confirmPaymentAction('delete', 1);
    if (confirmed) {
        event.target.submit();
    }
    
    return false;
}
</script>
</x-app-layout> 