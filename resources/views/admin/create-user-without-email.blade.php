<x-app-layout>
    <div class="card">
        <!-- User Creation Header Section -->
        <div style="
            background: linear-gradient(135deg, #206E39 0%, #28a745 100%); 
            color: white; 
            padding: 1.5rem 2rem; 
            border-radius: 12px; 
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(32, 110, 57, 0.2);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                <div>
                    <h1 style="margin: 0; font-size: 1.8rem; font-weight: 700;">Krijo Përdorues me Username</h1>
                    <p style="margin: 0; opacity: 0.9; font-size: 1rem;">Krijo përdorues të rinj me username për hyrje (Vetëm Super Admin)</p>
                </div>
                <div style="text-align: right;">
                    <a href="{{ route('admin.users') }}" 
                       style="background: rgba(255,255,255,0.2); color: white; border: none; padding: 0.75rem 1.25rem; border-radius: 6px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;"
                       onmouseover="this.style.background='rgba(255,255,255,0.3)';"
                       onmouseout="this.style.background='rgba(255,255,255,0.2)';">
                        <svg style="width: 18px; height: 18px;" fill="white" viewBox="0 0 24 24">
                            <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"/>
                        </svg>
                        Kthehu te Përdoruesit
                    </a>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div style="margin-bottom: 2rem; padding: 1rem; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 8px;">
                <strong>✅ Sukses!</strong> {{ session('success') }}
            </div>
        @endif

        <!-- Error Messages -->
        @if($errors->any())
            <div style="margin-bottom: 2rem; padding: 1rem; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 8px;">
                <strong>❌ Gabime:</strong>
                <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- User Creation Form - Matching Registration Layout -->
        <div style="background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <form method="POST" action="{{ route('admin.users.store-without-email') }}">
                @csrf

                <!-- Form Grid Layout - Matching Registration -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    
                    <!-- First Row: Emri, Mbiemri, Data e Lindjes -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333;">Emri</label>
                        <input type="text" 
                               name="first_name" 
                               value="{{ old('first_name') }}"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               onfocus="this.style.borderColor='#206E39';"
                               onblur="this.style.borderColor='#e0e0e0';"
                               placeholder="Emri"
                               required>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333;">Mbiemri</label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name') }}"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               onfocus="this.style.borderColor='#206E39';"
                               onblur="this.style.borderColor='#e0e0e0';"
                               placeholder="Mbiemri"
                               required>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333;">Data e Lindjes</label>
                        <input type="date" 
                               name="date_of_birth" 
                               value="{{ old('date_of_birth') }}"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               onfocus="this.style.borderColor='#206E39';"
                               onblur="this.style.borderColor='#e0e0e0';"
                               required>
                    </div>
                </div>

                <!-- Second Row: Adresa, Kodi Postar, Qyteti -->
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333;">Adresa</label>
                        <input type="text" 
                               name="address" 
                               value="{{ old('address') }}"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               onfocus="this.style.borderColor='#206E39';"
                               onblur="this.style.borderColor='#e0e0e0';"
                               placeholder="Rruga dhe numri"
                               required>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333;">Kodi Postar</label>
                        <input type="text" 
                               name="postal_code" 
                               value="{{ old('postal_code') }}"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               onfocus="this.style.borderColor='#206E39';"
                               onblur="this.style.borderColor='#e0e0e0';"
                               placeholder="Kodi Postar"
                               required>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333;">Qyteti</label>
                        <input type="text" 
                               name="city" 
                               value="{{ old('city') }}"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               onfocus="this.style.borderColor='#206E39';"
                               onblur="this.style.borderColor='#e0e0e0';"
                               placeholder="Qyteti"
                               required>
                    </div>
                </div>

                <!-- Third Row: Gjendja Civile (Full Width) -->
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333;">Gjendja Civile</label>
                    <select name="marital_status" 
                            style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s; background: white;"
                            onfocus="this.style.borderColor='#206E39';"
                            onblur="this.style.borderColor='#e0e0e0';"
                            required>
                        <option value="">Zgjidh gjendjen civile</option>
                        <option value="single" {{ old('marital_status') === 'single' ? 'selected' : '' }}>Beqar</option>
                        <option value="married" {{ old('marital_status') === 'married' ? 'selected' : '' }}>I Martuar</option>
                        <option value="divorced" {{ old('marital_status') === 'divorced' ? 'selected' : '' }}>I Divorcuar</option>
                        <option value="widowed" {{ old('marital_status') === 'widowed' ? 'selected' : '' }}>I Ve</option>
                    </select>
                </div>

                <!-- Fourth Row: Override Key (instead of Email), Numri i Telefonit -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #C19A61;">
                            🔐 Çelësi i Anashkalimit
                        </label>
                        <input type="password" 
                               name="override_key" 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #C19A61; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s; background: #fefbf7;"
                               onfocus="this.style.borderColor='#a67c52';"
                               onblur="this.style.borderColor='#C19A61';"
                               placeholder="Shkruaj çelësin special të anashkalimit..."
                               required>
                        <small style="color: #C19A61; font-size: 0.8rem; margin-top: 0.25rem; display: block;">
                            Kjo fushë kërkohet për të krijuar përdorues pa verifikim email
                        </small>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333;">Numri i Telefonit</label>
                        <input type="text" 
                               name="phone_number" 
                               value="{{ old('phone_number') }}"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               onfocus="this.style.borderColor='#206E39';"
                               onblur="this.style.borderColor='#e0e0e0';"
                               placeholder="+41 XX XXX XX XX"
                               required>
                    </div>
                </div>

                <!-- Fifth Row: Username, Fjalëkalimi, Konfirmo Fjalëkalimin -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333;">Username</label>
                        <input type="text" 
                               name="username" 
                               value="{{ old('username') }}"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               onfocus="this.style.borderColor='#206E39';"
                               onblur="this.style.borderColor='#e0e0e0';"
                               placeholder="username"
                               required>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333;">Fjalëkalimi</label>
                        <input type="password" 
                               name="password" 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               onfocus="this.style.borderColor='#206E39';"
                               onblur="this.style.borderColor='#e0e0e0';"
                               placeholder="Krijo fjalëkalimin"
                               required>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333;">Konfirmo Fjalëkalimin</label>
                        <input type="password" 
                               name="password_confirmation" 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               onfocus="this.style.borderColor='#206E39';"
                               onblur="this.style.borderColor='#e0e0e0';"
                               placeholder="Konfirmo fjalëkalimin"
                               required>
                    </div>
                </div>

                <!-- Submit Button - Matching Registration Style -->
                <div style="text-align: center; margin-top: 2rem;">
                    <button type="submit" 
                            style="background: #206E39; color: white; border: none; padding: 1rem 3rem; border-radius: 8px; font-weight: 600; font-size: 1.1rem; cursor: pointer; transition: all 0.3s ease; min-width: 200px;"
                            onmouseover="this.style.background='#1a5a2f';"
                            onmouseout="this.style.background='#206E39';">
                        Krijo Llogari
                    </button>
                </div>

                <!-- Cancel Link -->
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="{{ route('admin.users') }}" 
                       style="color: #C19A61; text-decoration: none; font-weight: 500; transition: color 0.3s;"
                       onmouseover="this.style.color='#a67c52';"
                       onmouseout="this.style.color='#C19A61';">
                        Anulo dhe kthehu te përdoruesit
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout> 