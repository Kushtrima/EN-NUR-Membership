<x-app-layout>
    <!-- Profile Header -->
    <div class="card" style="background: linear-gradient(135deg, #1F6E38 0%, #28a745 100%); color: white; text-align: center; padding: 2rem;">
        <div style="position: relative; display: inline-block; margin-bottom: 1rem;">
            <!-- User Avatar -->
            <div style="
                width: 120px; 
                height: 120px; 
                border-radius: 50%; 
                background: rgba(255,255,255,0.2); 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                font-size: 3rem; 
                font-weight: bold; 
                border: 4px solid rgba(255,255,255,0.3);
                margin: 0 auto;
            ">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            
            <!-- Membership Badge -->
            @if(auth()->user()->payments->where('payment_type', 'membership')->where('status', 'completed')->count() > 0)
                <div style="
                    position: absolute; 
                    bottom: 5px; 
                    right: 5px; 
                    background: #C19A61; 
                    color: white; 
                    border-radius: 50%; 
                    width: 35px; 
                    height: 35px; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    font-size: 1.2rem;
                    border: 3px solid white;
                ">
                    ✓
                </div>
            @endif
        </div>
        
        <h1 style="color: white; margin-bottom: 0.5rem; font-size: 2rem;">{{ auth()->user()->name }}</h1>
        <p style="opacity: 0.9; margin-bottom: 1rem;">{{ auth()->user()->email }}</p>
        
        <!-- Membership Status -->
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.15); padding: 0.5rem 1rem; border-radius: 25px; margin-bottom: 1rem;">
            @if(auth()->user()->payments->where('payment_type', 'membership')->where('status', 'completed')->count() > 0)
                <svg class="icon" style="color: #C19A61; font-size: 1.2rem;" viewBox="0 0 24 24">
                    <path d="M5 16L3 6l5.5 4L12 4l3.5 6L21 6l-2 10H5zm2.7-2h8.6l.9-5.4-2.1 1.7L12 8l-3.1 2.3-2.1-1.7L7.7 14z"/>
                </svg>
                <span style="font-weight: 600;">Active Member</span>
            @else
                <svg class="icon" style="color: #ffc107; font-size: 1.2rem;" viewBox="0 0 24 24">
                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"/>
                </svg>
                <span style="font-weight: 600;">Guest User</span>
            @endif
        </div>
        
        <div style="font-size: 0.9rem; opacity: 0.8;">
            Member since {{ auth()->user()->created_at->format('M d, Y') }}
        </div>
    </div>

    <!-- Profile Stats -->
    <div class="card">
        <h2 style="color: #1F6E38; margin-bottom: 1.5rem; text-align: center;">Përmbledhja e Llogarisë</h2>
        
        @php
            $userPayments = auth()->user()->payments->where('status', 'completed');
            $totalPaid = $userPayments->sum('amount') / 100;
            $totalDonations = $userPayments->where('payment_type', 'donation')->sum('amount') / 100;
            $membershipPayments = $userPayments->where('payment_type', 'membership');
        @endphp
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div style="text-align: center; padding: 2rem; background: rgba(31, 110, 56, 0.1); border: 2px solid #1F6E38; border-radius: 12px;">
                <div style="font-size: 2.5rem; color: #1F6E38; margin-bottom: 0.5rem;">
                    <svg class="icon icon-lg" style="color: #1F6E38;" viewBox="0 0 24 24">
                        <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                    </svg>
                </div>
                <div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem; color: #1F6E38;">CHF {{ number_format($totalPaid, 2) }}</div>
                <div style="color: #1F6E38;">Kontributet Totale</div>
            </div>
            
            <div style="text-align: center; padding: 2rem; background: rgba(31, 110, 56, 0.1); border: 2px solid #1F6E38; border-radius: 12px;">
                <div style="font-size: 2.5rem; color: #1F6E38; margin-bottom: 0.5rem;">
                    <svg class="icon icon-lg" style="color: #1F6E38;" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>
                <div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem; color: #1F6E38;">CHF {{ number_format($totalDonations, 2) }}</div>
                <div style="color: #1F6E38;">Dhurime të Bëra</div>
            </div>
            
            <div style="text-align: center; padding: 2rem; background: rgba(31, 110, 56, 0.1); border: 2px solid #1F6E38; border-radius: 12px;">
                <div style="font-size: 2.5rem; color: #1F6E38; margin-bottom: 0.5rem;">
                    <svg class="icon icon-lg" style="color: #1F6E38;" viewBox="0 0 24 24">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                    </svg>
                </div>
                <div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem; color: #1F6E38;">{{ $userPayments->count() }}</div>
                <div style="color: #1F6E38;">Pagesat Totale</div>
            </div>
            
            <div style="text-align: center; padding: 2rem; background: rgba(31, 110, 56, 0.1); border: 2px solid #1F6E38; border-radius: 12px;">
                <div style="font-size: 2.5rem; color: #1F6E38; margin-bottom: 0.5rem;">
                    @if(auth()->user()->hasVerifiedEmail())
                        <svg class="icon icon-lg" style="color: #1F6E38;" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    @else
                        <svg class="icon icon-lg" style="color: #1F6E38;" viewBox="0 0 24 24">
                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                        </svg>
                    @endif
                </div>
                <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 0.5rem; color: #1F6E38;">
                    @if(auth()->user()->hasVerifiedEmail())
                        I Verifikuar
                    @else
                        I Paverifikuar
                    @endif
                </div>
                <div style="color: #1F6E38;">Statusi i Email-it</div>
            </div>
        </div>
        

    </div>

    <!-- Profile Settings Tabs -->
    <div class="card">
        <div style="border-bottom: 2px solid #f8f9fa; margin-bottom: 2rem;">
            <div style="display: flex; gap: 0; border-bottom: 1px solid #e9ecef;">
                <button class="tab-btn active" onclick="showTab('profile-info')" style="
                    padding: 1rem 1.5rem; 
                    background: none; 
                    border: none; 
                    border-bottom: 3px solid #1F6E38; 
                    color: #1F6E38; 
                    font-weight: 600;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                ">
                    <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg> Informacionet e Profilit
                </button>
                <button class="tab-btn" onclick="showTab('security')" style="
                    padding: 1rem 1.5rem; 
                    background: none; 
                    border: none; 
                    border-bottom: 3px solid transparent; 
                    color: #6c757d; 
                    font-weight: 600;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                ">
                    <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                    </svg> Siguria
                </button>
                <button class="tab-btn" onclick="showTab('danger')" style="
                    padding: 1rem 1.5rem; 
                    background: none; 
                    border: none; 
                    border-bottom: 3px solid transparent; 
                    color: #6c757d; 
                    font-weight: 600;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                ">
                    <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg> Cilësimet e Llogarisë
                </button>
            </div>
        </div>

        <!-- Profile Information Tab -->
        <div id="profile-info" class="tab-content">
            <div style="max-width: 600px; margin: 0 auto;">
                <h3 style="color: #1F6E38; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg> Përditëso Informacionet e Profilit
                </h3>
                
                <form method="post" action="{{ route('profile.update') }}" style="background: rgba(31, 110, 56, 0.1); border: 2px solid #1F6E38; padding: 2rem; border-radius: 12px;">
                    @method('patch')
                    @csrf

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="name" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1F6E38; display: flex; align-items: center; gap: 0.5rem;">
                            <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                            </svg>Emri i Plotë
                        </label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #1F6E38; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease; background: white;"
                               onfocus="this.style.borderColor='#28a745'; this.style.boxShadow='0 0 0 3px rgba(31, 110, 56, 0.1)'"
                               onblur="this.style.borderColor='#1F6E38'; this.style.boxShadow='none'">
                        @error('name')
                            <div style="color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.25rem;">
                                <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                                </svg>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="email" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1F6E38; display: flex; align-items: center; gap: 0.5rem;">
                            <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                <path d="M20 4H4c-1.1 0-1.99.89-1.99 2L2 18c0 1.1.89 2 2 2h16c1.1 0 2-.89 2-2V6c0-1.1-.89-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                            </svg>Adresa e Email-it
                        </label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #1F6E38; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease; background: white;"
                               onfocus="this.style.borderColor='#28a745'; this.style.boxShadow='0 0 0 3px rgba(31, 110, 56, 0.1)'"
                               onblur="this.style.borderColor='#1F6E38'; this.style.boxShadow='none'">
                        @error('email')
                            <div style="color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.25rem;">
                                <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                                </svg>{{ $message }}
                            </div>
                        @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div style="margin-top: 1rem; padding: 1rem; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px;">
                                <p style="margin: 0; color: #856404; display: flex; align-items: center; gap: 0.5rem;">
                                    <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                                    </svg>Adresa juaj e email-it nuk është e verifikuar.
                                </p>
                                <button form="send-verification" style="margin-top: 0.5rem; background: #ffc107; color: #212529; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                                    <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                        <path d="M20 4H4c-1.1 0-1.99.89-1.99 2L2 18c0 1.1.89 2 2 2h16c1.1 0 2-.89 2-2V6c0-1.1-.89-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                    </svg> Ridërgo Email-in e Verifikimit
                                </button>

                                @if (session('status') === 'verification-link-sent')
                                    <p style="margin-top: 0.5rem; color: #28a745; display: flex; align-items: center; gap: 0.5rem;">
                                        <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>Linku i verifikimit u dërgua në email-in tuaj!
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div style="text-align: center;">
                        <button type="submit" style="background: #1F6E38; color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem;"
                                onmouseover="this.style.background='#28a745'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='#1F6E38'; this.style.transform='translateY(0)'">
                            <svg class="icon" style="color: white;" viewBox="0 0 24 24">
                                <path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/>
                            </svg> Ruaj Ndryshimet
                        </button>

                        @if (session('status') === 'profile-updated')
                            <div style="margin-top: 1rem; color: #28a745; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>Profili u përditësua me sukses!
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Tab -->
        <div id="security" class="tab-content" style="display: none;">
            <div style="max-width: 600px; margin: 0 auto;">
                <h3 style="color: #1F6E38; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                    </svg> Ndrysho Fjalëkalimin
                </h3>
                <p style="color: #1F6E38; margin-bottom: 2rem; text-align: center;">
                    Mbajeni llogarinë tuaj të sigurt me një fjalëkalim të fortë
                </p>
                
                <form method="post" action="{{ route('password.update') }}" style="background: rgba(31, 110, 56, 0.1); border: 2px solid #1F6E38; padding: 2rem; border-radius: 12px;">
                    @method('put')
                    @csrf

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="update_password_current_password" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1F6E38; display: flex; align-items: center; gap: 0.5rem;">
                            <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                <path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                            </svg>Fjalëkalimi Aktual
                        </label>
                        <input id="update_password_current_password" name="current_password" type="password" 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #1F6E38; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease; background: white;"
                               onfocus="this.style.borderColor='#28a745'; this.style.boxShadow='0 0 0 3px rgba(31, 110, 56, 0.1)'"
                               onblur="this.style.borderColor='#1F6E38'; this.style.boxShadow='none'">
                        @error('current_password', 'updatePassword')
                            <div style="color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.25rem;">
                                <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                                </svg>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="update_password_password" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1F6E38; display: flex; align-items: center; gap: 0.5rem;">
                            <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                <path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                            </svg>Fjalëkalimi i Ri
                        </label>
                        <input id="update_password_password" name="password" type="password" 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #1F6E38; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease; background: white;"
                               onfocus="this.style.borderColor='#28a745'; this.style.boxShadow='0 0 0 3px rgba(31, 110, 56, 0.1)'"
                               onblur="this.style.borderColor='#1F6E38'; this.style.boxShadow='none'">
                        @error('password', 'updatePassword')
                            <div style="color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.25rem;">
                                <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                                </svg>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label for="update_password_password_confirmation" style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1F6E38; display: flex; align-items: center; gap: 0.5rem;">
                            <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                <path d="M12 6v3l4-4-4-4v3c-4.42 0-8 3.58-8 8 0 1.57.46 3.03 1.24 4.26L6.7 14.8c-.45-.83-.7-1.79-.7-2.8 0-3.31 2.69-6 6-6zm6.76 1.74L17.3 9.2c.44.84.7 1.79.7 2.8 0 3.31-2.69 6-6 6v-3l-4 4 4 4v-3c4.42 0 8-3.58 8-8 0-1.57-.46-3.03-1.24-4.26z"/>
                            </svg>Konfirmo Fjalëkalimin e Ri
                        </label>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #1F6E38; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease; background: white;"
                               onfocus="this.style.borderColor='#28a745'; this.style.boxShadow='0 0 0 3px rgba(31, 110, 56, 0.1)'"
                               onblur="this.style.borderColor='#1F6E38'; this.style.boxShadow='none'">
                        @error('password_confirmation', 'updatePassword')
                            <div style="color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.25rem;">
                                <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                                </svg>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div style="text-align: center;">
                        <button type="submit" style="background: #1F6E38; color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem;"
                                onmouseover="this.style.background='#28a745'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='#1F6E38'; this.style.transform='translateY(0)'">
                            <svg class="icon" style="color: white;" viewBox="0 0 24 24">
                                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                            </svg> Përditëso Fjalëkalimin
                        </button>

                        @if (session('status') === 'password-updated')
                            <div style="margin-top: 1rem; color: #28a745; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>Fjalëkalimi u përditësua me sukses!
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Danger Zone Tab -->
        <div id="danger" class="tab-content" style="display: none;">
            <div style="max-width: 600px; margin: 0 auto;">
                <h3 style="color: #dc3545; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg> Zona e Rrezikshme
                </h3>
                
                <div style="background: #f8d7da; border: 2px solid #f5c6cb; padding: 2rem; border-radius: 12px;">
                    <h4 style="color: #721c24; margin-bottom: 1rem;">Fshi Llogarinë</h4>
                    <p style="color: #721c24; margin-bottom: 1.5rem; line-height: 1.5;">
                        <svg class="icon" style="color: #1F6E38;" viewBox="0 0 24 24">
                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                        </svg> <strong>Paralajmërim:</strong> Ky veprim është i përhershëm dhe nuk mund të zhbëhet. Të gjitha të dhënat tuaja, duke përfshirë historikun e pagesave dhe informacionet e anëtarësisë, do të fshihen përfundimisht.
                    </p>
                    
                    <div style="background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                        <h5 style="color: #495057; margin-bottom: 0.5rem;">Çfarë do të fshihet:</h5>
                        <ul style="color: #6c757d; margin: 0; padding-left: 1.5rem;">
                            <li>Informacionet e profilit tuaj</li>
                            <li>Historiku i pagesave dhe faturat</li>
                            <li>Statusi i anëtarësisë</li>
                            <li>Të gjitha të dhënat e llogarisë</li>
                        </ul>
                    </div>

                    <div style="text-align: center;">
                        <button onclick="confirmDelete()" style="background: #dc3545; color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem;"
                                onmouseover="this.style.background='#c82333'; this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.background='#dc3545'; this.style.transform='translateY(0)'">
                            <svg class="icon" style="color: white;" viewBox="0 0 24 24">
                                <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                            </svg> Fshi Llogarinë Time
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Forms -->
    <form id="send-verification" method="post" action="{{ route('verification.send') }}" style="display: none;">
        @csrf
    </form>

    <form id="delete-user-form" method="post" action="{{ route('profile.destroy') }}" style="display: none;">
        @csrf
        @method('delete')
        <input type="password" name="password" id="delete-password" required>
    </form>

    <style>
        /* SVG Icons */
        .icon {
            width: 1em;
            height: 1em;
            display: inline-block;
            vertical-align: middle;
            fill: currentColor;
        }
        
        .icon-lg {
            width: 1.5em;
            height: 1.5em;
        }
        
        .tab-btn:hover {
            color: #1F6E38 !important;
            background: rgba(31, 110, 56, 0.05) !important;
        }
        
        .tab-btn.active {
            color: #1F6E38 !important;
            border-bottom-color: #1F6E38 !important;
        }
        
        .form-group input:focus {
            outline: none !important;
        }
        
        @media (max-width: 768px) {
            .tab-btn {
                padding: 0.75rem 1rem !important;
                font-size: 0.9rem !important;
            }
        }
    </style>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.style.borderBottomColor = 'transparent';
                btn.style.color = '#6c757d';
            });
            
            // Show selected tab
            document.getElementById(tabName).style.display = 'block';
            
            // Add active class to clicked button
            event.target.classList.add('active');
            event.target.style.borderBottomColor = '#1F6E38';
            event.target.style.color = '#1F6E38';
        }
        
        function confirmDelete() {
            const password = prompt('Për të konfirmuar fshirjen e llogarisë, ju lutemi shkruani fjalëkalimin tuaj:');
            if (password) {
                if (confirm('PARALAJMËRIM I FUNDIT: Kjo do të fshijë përfundimisht llogarinë tuaj dhe të gjitha të dhënat. Jeni absolutisht i sigurt?')) {
                    document.getElementById('delete-password').value = password;
                    document.getElementById('delete-user-form').submit();
                }
            }
        }
    </script>
</x-app-layout> 