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
            <div style="background: rgba(40, 167, 69, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #28a745;">{{ $stats['completed'] }}</div>
                <div style="font-size: 0.9rem; color: #666;">E Përfunduar</div>
            </div>
            <div style="background: rgba(255, 193, 7, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #ffc107;">{{ $stats['pending'] }}</div>
                <div style="font-size: 0.9rem; color: #666;">Në Pritje</div>
            </div>
            <div style="background: rgba(220, 53, 69, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #dc3545;">{{ $stats['failed'] }}</div>
                <div style="font-size: 0.9rem; color: #666;">Dështuar</div>
            </div>
            @if($stats['total_amount'] > 0)
                <div style="background: rgba(193, 154, 97, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #C19A61;">CHF {{ number_format($stats['total_amount'], 2) }}</div>
                    <div style="font-size: 0.9rem; color: #666;">Shuma Totale</div>
                </div>
            @endif
            @if($stats['membership_payments'] > 0)
                <div style="background: rgba(31, 110, 56, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #1F6E38;">{{ $stats['membership_payments'] }}</div>
                    <div style="font-size: 0.9rem; color: #666;">Anëtarësi</div>
                </div>
            @endif
            @if($stats['donation_total'] > 0)
                <div style="background: rgba(193, 154, 97, 0.1); border-radius: 8px; padding: 1rem; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #C19A61;">CHF {{ number_format($stats['donation_total'], 2) }}</div>
                    <div style="font-size: 0.9rem; color: #666;">Dhurime</div>
                </div>
            @endif
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