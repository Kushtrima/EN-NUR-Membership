<x-app-layout>
    <div style="max-width: 800px; margin: 0 auto; padding: 2rem 0;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 80px; height: 80px; background-color: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <svg style="width: 40px; height: 40px; color: white;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8M12,10A2,2 0 0,0 10,12A2,2 0 0,0 12,14A2,2 0 0,0 14,12A2,2 0 0,0 12,10M21,4H3A2,2 0 0,0 1,6V18A2,2 0 0,0 3,20H21A2,2 0 0,0 23,18V6A2,2 0 0,0 21,4M21,18H3V6H21V18Z"/>
                </svg>
            </div>
            <h1 style="font-size: 2rem; font-weight: bold; color: #1F6E38; margin-bottom: 0.5rem;">Udhëzimet për Pagesën me Kesh</h1>
            <p style="font-size: 1.125rem; color: #6c757d;">Përfundo pagesën e {{ $payment->payment_type }} me kesh</p>
        </div>

        <!-- Payment Details Card -->
        <div class="card" style="margin-bottom: 2rem;">
            <div style="background-color: #28a745; padding: 1.5rem; border-bottom: 2px solid #1F6E38; border-radius: 8px 8px 0 0;">
                <h2 style="font-size: 1.25rem; font-weight: 600; color: white; display: flex; align-items: center;">
                    <svg style="width: 20px; height: 20px; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Detajet e Pagesës
                </h2>
            </div>
            <div style="padding: 1.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <p style="font-size: 0.875rem; font-weight: 500; color: #6c757d; margin-bottom: 0.25rem;">ID e Pagesës</p>
                        <p style="font-size: 1.125rem; font-family: monospace; color: #1F6E38;">{{ $payment->id }}</p>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; font-weight: 500; color: #6c757d; margin-bottom: 0.25rem;">Shuma</p>
                        <p style="font-size: 1.5rem; font-weight: bold; color: #1F6E38;">{{ $payment->formatted_amount }}</p>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; font-weight: 500; color: #6c757d; margin-bottom: 0.25rem;">Lloji</p>
                        <p style="font-size: 1.125rem; color: #1F6E38; text-transform: capitalize;">{{ $payment->payment_type }}</p>
                    </div>
                    <div>
                        <p style="font-size: 0.875rem; font-weight: 500; color: #6c757d; margin-bottom: 0.25rem;">Statusi</p>
                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7;">
                            <svg style="width: 12px; height: 12px; margin-right: 0.25rem;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            Pagesa në Pritje
                        </span>
                    </div>
                </div>
                
                <!-- Reference Number -->
                <div style="margin-top: 1.5rem; padding: 1rem; background-color: #f8f9fa; border-radius: 8px; border: 2px dashed #1F6E38;">
                    <p style="font-size: 0.875rem; font-weight: 500; color: #6c757d; margin-bottom: 0.5rem;">Referenca e Pagesës (Ju lutem sillni këtë):</p>
                    <p style="font-size: 1.25rem; font-family: monospace; font-weight: bold; color: #1F6E38; background-color: white; padding: 0.5rem 0.75rem; border-radius: 4px; border: 1px solid #ddd;">CASH-{{ $payment->id }}</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; flex-direction: row; gap: 1rem; justify-content: center; align-items: center;">
            <a href="{{ route('payment.index') }}" 
               style="display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; border: 1px solid #6c757d; border-radius: 8px; text-decoration: none; font-size: 1rem; font-weight: 500; color: #6c757d; background-color: white; transition: all 0.2s ease;"
               onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#5a6268'; this.style.color='#495057';"
               onmouseout="this.style.backgroundColor='white'; this.style.borderColor='#6c757d'; this.style.color='#6c757d';">
                Shiko Pagesat e Mia
            </a>
            
            <a href="{{ route('dashboard') }}" 
               style="display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; text-decoration: none; font-size: 1rem; font-weight: 500; color: white; background-color: #1F6E38; transition: all 0.2s ease;"
               onmouseover="this.style.backgroundColor='#155724'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';"
               onmouseout="this.style.backgroundColor='#1F6E38'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                Shko te Paneli
            </a>
        </div>
    </div>
</x-app-layout>
