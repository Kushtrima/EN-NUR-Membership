<x-app-layout>
    <div class="card" style="max-width: 800px; margin: 2rem auto;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="color: #1F6E38; margin-bottom: 0.5rem;">Mirë se vini në EN NUR!</h1>
            <p style="color: #666; font-size: 1.1rem;">Ju lutemi rishikoni dhe pranoni kushtet tona për të përfunduar regjistrimin tuaj</p>
        </div>

        <!-- Progress Indicator -->
        <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 30px; height: 30px; background: #28a745; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">✓</div>
                <span style="color: #28a745; font-weight: 500;">Email-i u Verifikua</span>
                
                <div style="width: 40px; height: 2px; background: #dee2e6;"></div>
                
                <div style="width: 30px; height: 30px; background: #1F6E38; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">2</div>
                <span style="color: #1F6E38; font-weight: 500;">Pranoni Kushtet</span>
                
                <div style="width: 40px; height: 2px; background: #dee2e6;"></div>
                
                <div style="width: 30px; height: 30px; background: #dee2e6; color: #6c757d; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">3</div>
                <span style="color: #6c757d; font-weight: 500;">Hyrja në Dashboard</span>
            </div>
        </div>

        <form method="POST" action="{{ route('terms.accept') }}">
            @csrf
            
            <!-- Terms and Conditions Section -->
            <div style="margin-bottom: 2rem; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden;">
                <div style="background: #1F6E38; color: white; padding: 1rem;">
                    <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        Kushtet e përgjithshme të Kontratës
                    </h3>
                </div>
                <div style="padding: 1.5rem; max-height: 400px; overflow-y: auto; background: #f8f9fa;">
                    <div style="line-height: 1.6; color: #333; font-size: 14px;">
                        <h4 style="color: #1F6E38; margin-bottom: 1rem;">A. Vendimet dhe Kushtet:</h4>
                        <p>Vendimet janë marrë nga kryesia e Xhamisë En-Nur, me miratimin e Anëtarëve të Kryesisë.</p>
                        <p>Kontrata është një lehtësim për Anëtarët e Xhamisë En-Nur-Diessenhofen, të cilëve i ndodh një fatkeqësi familjare.</p>
                        
                        <h4 style="color: #1F6E38; margin-bottom: 1rem;">B. Kriteret dhe pagesa:</h4>
                        <p>Kushtet për regjistrimi në këtë Fondi (Kufoma) janë:</p>
                        <ul>
                            <li>Formulari duhet tërësisht të plotësohet.</li>
                            <li>Pagesa duhet kryer në afat prej 180 ditëve edhe atë nga fillimi i Vitit deri në fund të muajit të Gjashtë. Pas 180 ditëve Xhamia nuk merr asnjë përgjegjësi për pagesën e transportit si të Vdekurit (vendimtare është data e bankës).</li>
                            <li>Nëse brenda 3 viteve Anëtari nuk e ka kryer pagesën e kufomës, Xhamia nuk merr përgjegjësi për Kufomën. (shembull: 2016, 2017, 2018 në qoftë se mungon pagesa e një viti, në 3 vite e fundit nga viti aktual)</li>
                            <li>Të anëtaruarit e ri, në vitin e parë nuk përfitojnë të drejtën nga Fondi i kufomës. Këshillohen të bëjnë pagesën te Fondi i Kufomës në mënyrë individuale.</li>
                            <li>Vend qëndrimi Zyrtar/Legjitim (Vizë B,C ose Pasaportë Zvicerane) në Zvicër.</li>
                        </ul>
                        <p>Kontrata hynë në Fuqi pasi që të jetë plotësuar, nënshkruar Formulari, si dhe kryerja e pagesës në kohë. Nëse nuk jemi senior në afatet dhe kushtet e caktuara, Xhamia dhe Fondi nuk merr përgjegjësi.</p>
                        <p><strong>Çmimi vjetor për një Familje, Anëtarët e rregulltë në Xhamin En-Nur kushton 360Fr.- (300Fr.- Anëtarësia + 60Fr.- fondi i kufomës).</strong></p>
                        <p>Në rast (Gëzimi/Lindje) një anëtarë të ri familjar apo Shkurrorzimi, obligohen anëtari të kërkoj/plotësoj një Formular të ri.</p>
                        <p>Xhamia EN-Nur për personat që vdesin si pasojë e rasteve në vijim nuk merr përsipër asnjë harxhim: Vetëvrasje Konsumimi i alkoolit, drogës ose medikamenteve (überdosis), Pjesëmarrje në greva ose trazira, Pjesëmarrje në gara dhe trajnime me motoçikleta, bungy jumping, fluturim me parashutë si dhe gara me anije.</p>
                        
                        <h4 style="color: #1F6E38; margin-bottom: 1rem;">C. Kush ka drejtën për këto Shërbime:</h4>
                        <p>Anëtarë së bashku me Fëmijët e tyre deri në moshën 19 vjeçare ose në Lehre dhe të sapo lindurit (deri në moshën 1 vjeçare të cilët nuk janë akoma të regjistruar).</p>
                        <p>Fëmijët e punësuar dhe ata të cilët martohen, e humbin këtë të drejt. Obligohen të anëtarësohen.</p>
                        
                        <h4 style="color: #1F6E38; margin-bottom: 1rem;">D. Shërbimet:</h4>
                        <p>Blerja e Tabutit, Qefini + Larja e Kufomës, Qiraja për dhenë e Larjes, Shërbimet e Transportit të Kufomës, Taksat, Aeroporti + Aeroplani deri në Kryeqytetin e Vendlindjes. Transporti prej Aeroportit Kryeqytetit deri në Vendilindje duhet të bëhet nga Familja e të Vdekurit.</p>
                        
                        <h4 style="color: #1F6E38; margin-bottom: 1rem;">E. Familjarët obligohen:</h4>
                        <p>Në rast të vdekjes së ndonjë Anëtari, menjëherë të njoftohet Kryetari apo Kryesia. Rregullorja e lartë, që përbëhet nga kto nene, është përpiluar nga ana e Xhamisë EN-Nur dhe është miratuar nga anëtarët e Xhamisë En-Nur. Ndryshimet që realizohen në fondin EN-Nur bëhen dhe realizohen nga ana e kryesisë së Xhamisë En-Nur.</p>
                    </div>
                </div>
                <div style="padding: 1rem; background: white; border-top: 1px solid #dee2e6;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="accept_terms" value="1" required 
                               style="width: 18px; height: 18px; accent-color: #1F6E38;">
                        <span style="font-weight: 500;">Kam lexuar dhe pranoj Kushtet e përgjithshme të Kontratës</span>
                    </label>
                    @error('accept_terms')
                        <div style="color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                <button type="submit" 
                        style="background: #1F6E38; color: white; border: none; padding: 1rem 2rem; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;"
                        onmouseover="this.style.background='#0d4d1f'"
                        onmouseout="this.style.background='#1F6E38'">
                    Pranoni dhe Vazhdoni në Dashboard
                </button>
            </div>
        </form>

        <!-- Footer Note -->
        <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #dee2e6; color: #666; font-size: 0.9rem;">
            <p>Duke pranuar këto kushte, ju konfirmoni se jeni të paktën 18 vjeç dhe keni kapacitetin ligjor për të hyrë në këtë marrëveshje.</p>
            <p>Nëse keni ndonjë pyetje, ju lutemi na kontaktoni në <a href="mailto:info@xhamia-en-nur.ch" style="color: #1F6E38;">info@xhamia-en-nur.ch</a></p>
        </div>
    </div>
</x-app-layout> 