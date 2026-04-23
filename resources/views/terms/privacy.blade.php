<x-app-layout>
    <div class="card" style="max-width: 800px; margin: 2rem auto;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="color: #1F6E38; margin-bottom: 0.5rem;">Politika e Privatësisë</h1>
            <p style="color: #666; font-size: 1rem;">Xhamia EN-Nur-Diessenhofen</p>
        </div>

        <div style="padding: 1.5rem; background: #f8f9fa; border-radius: 8px; line-height: 1.7;">
            <h3 style="color: #1F6E38; margin-bottom: 1rem;">1. Të dhënat që mbledhim</h3>
            <p>Për të ofruar shërbimet e anëtarësimit dhe Fondit të Kufomës, mbledhim dhe ruajmë të dhënat e mëposhtme:</p>
            <ul style="padding-left: 1.5rem;">
                <li>Emri, mbiemri dhe data e lindjes</li>
                <li>Adresa, kodi postar, qyteti</li>
                <li>Numri i telefonit dhe email-i</li>
                <li>Statusi martesor</li>
                <li>Historiku i pagesave të anëtarësimit</li>
            </ul>

            <h3 style="color: #1F6E38; margin-top: 1.5rem; margin-bottom: 1rem;">2. Si i përdorim</h3>
            <p>Të dhënat tuaja përdoren vetëm për:</p>
            <ul style="padding-left: 1.5rem;">
                <li>Administrimin e anëtarësimit dhe Fondit të Kufomës</li>
                <li>Kontaktimin tuaj për rinovim, njoftime ose në rastet e Fondit</li>
                <li>Dorëzimin e faturave dhe konfirmimeve të pagesave</li>
                <li>Përmbushjen e detyrimeve ligjore kontabël dhe fiskale sipas legjislacionit Zviceran</li>
            </ul>

            <h3 style="color: #1F6E38; margin-top: 1.5rem; margin-bottom: 1rem;">3. Me kë i ndajmë</h3>
            <p>Të dhënat tuaja nuk u shiten dhe nuk u jepen palëve të treta për qëllime marketingu. Ato mund të ndahen vetëm me:</p>
            <ul style="padding-left: 1.5rem;">
                <li>Ofruesit e pagesave (Stripe, PayPal, Twint) për përpunimin e transaksioneve</li>
                <li>Autoritetet Zvicerane kur kërkohet me ligj</li>
            </ul>

            <h3 style="color: #1F6E38; margin-top: 1.5rem; margin-bottom: 1rem;">4. Siguria</h3>
            <p>Të dhënat ruhen në serverë të sigurt me enkriptim TLS/HTTPS dhe kontrolluar aksesi. Të dhënat e pagesave (numra karte) nuk ruhen në sistemet tona — ato përpunohen drejtpërdrejt nga ofruesit e pagesave.</p>

            <h3 style="color: #1F6E38; margin-top: 1.5rem; margin-bottom: 1rem;">5. Të drejtat tuaja</h3>
            <p>Ju keni të drejtën të:</p>
            <ul style="padding-left: 1.5rem;">
                <li>Kërkoni një kopje të të dhënave tuaja</li>
                <li>Kërkoni korrigjimin e të dhënave të pasakta</li>
                <li>Kërkoni fshirjen e llogarisë suaj (në kufij të detyrimeve ligjore për ruajtjen e dokumenteve financiare)</li>
            </ul>

            <h3 style="color: #1F6E38; margin-top: 1.5rem; margin-bottom: 1rem;">6. Kontakti</h3>
            <p>Për çdo pyetje në lidhje me privatësinë ose për të ushtruar të drejtat tuaja, ju lutem kontaktoni Kryesinë e Xhamisë EN-Nur.</p>

            <p style="margin-top: 2rem; font-size: 0.9rem; color: #666;"><em>Kjo politikë mund të përditësohet. Versioni aktual: {{ date('Y-m-d') }}.</em></p>
        </div>

        <div style="margin-top: 2rem; text-align: center;">
            <a href="{{ url()->previous() }}" style="color: #1F6E38; text-decoration: none; font-weight: 500;">&larr; Kthehu</a>
        </div>
    </div>
</x-app-layout>
