<x-app-layout>
    <div class="card" style="max-width: 800px; margin: 2rem auto;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="color: #1F6E38; margin-bottom: 0.5rem;">Welcome to EN NUR!</h1>
            <p style="color: #666; font-size: 1.1rem;">Please review and accept our terms to complete your registration</p>
        </div>

        <!-- Progress Indicator -->
        <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 30px; height: 30px; background: #28a745; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">✓</div>
                <span style="color: #28a745; font-weight: 500;">Email Verified</span>
                
                <div style="width: 40px; height: 2px; background: #dee2e6;"></div>
                
                <div style="width: 30px; height: 30px; background: #1F6E38; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">2</div>
                <span style="color: #1F6E38; font-weight: 500;">Accept Terms</span>
                
                <div style="width: 40px; height: 2px; background: #dee2e6;"></div>
                
                <div style="width: 30px; height: 30px; background: #dee2e6; color: #6c757d; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">3</div>
                <span style="color: #6c757d; font-weight: 500;">Dashboard Access</span>
            </div>
        </div>

        <form method="POST" action="{{ route('terms.accept') }}">
            @csrf
            
            <!-- Terms and Conditions Section -->
            <div style="margin-bottom: 2rem; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden;">
                <div style="background: #1F6E38; color: white; padding: 1rem;">
                    <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        Terms and Conditions
                    </h3>
                </div>
                <div style="padding: 1.5rem; max-height: 300px; overflow-y: auto; background: #f8f9fa;">
                    <div style="line-height: 1.6; color: #333;">
                        <h4 style="color: #1F6E38; margin-bottom: 1rem;">1. Membership Agreement</h4>
                        <p>By joining EN NUR, you agree to maintain active membership through annual payments and participate constructively in our community activities.</p>
                        
                        <h4 style="color: #1F6E38; margin-bottom: 1rem;">2. Payment Terms</h4>
                        <p>Membership fees are CHF 350 annually. Donations are welcome and help support our community initiatives. All payments are processed securely.</p>
                        
                        <h4 style="color: #1F6E38; margin-bottom: 1rem;">3. Community Guidelines</h4>
                        <p>Members must respect fellow community members, participate in good faith, and contribute positively to our shared goals and activities.</p>
                        
                        <h4 style="color: #1F6E38; margin-bottom: 1rem;">4. Data Protection</h4>
                        <p>We protect your personal information and use it only for membership management, communication, and community activities as outlined in our Privacy Policy.</p>
                        
                        <h4 style="color: #1F6E38; margin-bottom: 1rem;">5. Account Responsibilities</h4>
                        <p>You are responsible for maintaining the security of your account credentials and promptly notifying us of any unauthorized access.</p>
                    </div>
                </div>
                <div style="padding: 1rem; background: white; border-top: 1px solid #dee2e6;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="accept_terms" value="1" required 
                               style="width: 18px; height: 18px; accent-color: #1F6E38;">
                        <span style="font-weight: 500;">I have read and accept the <a href="{{ route('terms.full') }}" target="_blank" style="color: #1F6E38; text-decoration: none;">Terms and Conditions</a></span>
                    </label>
                    @error('accept_terms')
                        <div style="color: #dc3545; font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Privacy Policy Section -->
            <div style="margin-bottom: 2rem; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden;">
                <div style="background: #C19A61; color: white; padding: 1rem;">
                    <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        Privacy Policy
                    </h3>
                </div>
                <div style="padding: 1.5rem; max-height: 200px; overflow-y: auto; background: #f8f9fa;">
                    <div style="line-height: 1.6; color: #333;">
                        <h4 style="color: #C19A61; margin-bottom: 1rem;">Data Collection</h4>
                        <p>We collect only necessary information for membership management: name, email, payment details, and communication preferences.</p>
                        
                        <h4 style="color: #C19A61; margin-bottom: 1rem;">Data Usage</h4>
                        <p>Your information is used exclusively for membership services, community communications, and payment processing. We never sell or share your data with third parties.</p>
                        
                        <h4 style="color: #C19A61; margin-bottom: 1rem;">Your Rights</h4>
                        <p>You have the right to access, update, or delete your personal information at any time through your account settings or by contacting us.</p>
                    </div>
                </div>
                <div style="padding: 1rem; background: white; border-top: 1px solid #dee2e6;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="accept_privacy" value="1" required 
                               style="width: 18px; height: 18px; accent-color: #C19A61;">
                        <span style="font-weight: 500;">I have read and accept the <a href="{{ route('terms.privacy') }}" target="_blank" style="color: #C19A61; text-decoration: none;">Privacy Policy</a></span>
                    </label>
                    @error('accept_privacy')
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
                    Accept and Continue to Dashboard
                </button>
            </div>
        </form>

        <!-- Footer Note -->
        <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #dee2e6; color: #666; font-size: 0.9rem;">
            <p>By accepting these terms, you confirm that you are at least 18 years old and have the legal capacity to enter into this agreement.</p>
            <p>If you have any questions, please contact us at <a href="mailto:info@en-nur.org" style="color: #1F6E38;">info@en-nur.org</a></p>
        </div>
    </div>
</x-app-layout> 