@props(['currentStep' => 1])

<div class="payment-steps-container" style="display: flex; justify-content: center; align-items: center; margin-bottom: 2rem; padding: 1.5rem; background: rgba(31, 110, 56, 0.05); border-radius: 12px; flex-wrap: wrap; gap: 1rem;">
    <!-- Step 1: Select Amount -->
    <div class="payment-step" style="display: flex; align-items: center; flex: 1; min-width: 200px;">
        <div class="step-circle" style="
            width: 45px; 
            height: 45px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold; 
            color: white;
            background-color: {{ $currentStep >= 1 ? '#1F6E38' : '#6c757d' }};
            {{ $currentStep == 1 ? 'box-shadow: 0 0 0 4px rgba(31, 110, 56, 0.3); transform: scale(1.1); animation: pulse 2s infinite;' : '' }}
            transition: all 0.3s ease;
            position: relative;
        ">
            @if($currentStep > 1)
                <svg style="width: 20px; height: 20px;" fill="white" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
            @else
                1
            @endif
        </div>
        <div class="step-content" style="margin-left: 1rem; text-align: left;">
            <div class="step-title" style="font-weight: 600; color: {{ $currentStep >= 1 ? '#1F6E38' : '#6c757d' }}; font-size: 1rem; margin-bottom: 0.25rem;">
                Zgjidh Shumën
            </div>
            <div class="step-description" style="font-size: 0.8rem; color: #6c757d; line-height: 1.3;">
                Zgjidh anëtarësinë ose dhurimin
            </div>
        </div>
    </div>

    <!-- Connector Line -->
    <div class="connector-line" style="
        width: 80px; 
        height: 4px; 
        background: linear-gradient(to right, {{ $currentStep >= 2 ? '#1F6E38' : '#e9ecef' }} 0%, {{ $currentStep >= 2 ? '#1F6E38' : '#e9ecef' }} 100%); 
        margin: 0 0.5rem;
        border-radius: 2px;
        transition: all 0.5s ease;
        flex-shrink: 0;
        position: relative;
    "></div>

    <!-- Step 2: Choose Payment -->
    <div class="payment-step" style="display: flex; align-items: center; flex: 1; min-width: 200px;">
        <div class="step-circle" style="
            width: 45px; 
            height: 45px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold; 
            color: white;
            background-color: {{ $currentStep >= 2 ? '#1F6E38' : '#6c757d' }};
            {{ $currentStep == 2 ? 'box-shadow: 0 0 0 4px rgba(31, 110, 56, 0.3); transform: scale(1.1); animation: pulse 2s infinite;' : '' }}
            transition: all 0.3s ease;
        ">
            @if($currentStep > 2)
                <svg style="width: 20px; height: 20px;" fill="white" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
            @else
                2
            @endif
        </div>
        <div class="step-content" style="margin-left: 1rem; text-align: left;">
            <div class="step-title" style="font-weight: 600; color: {{ $currentStep >= 2 ? '#1F6E38' : '#6c757d' }}; font-size: 1rem; margin-bottom: 0.25rem;">
                Zgjidh Pagesën
            </div>
            <div class="step-description" style="font-size: 0.8rem; color: #6c757d; line-height: 1.3;">
                Zgjidh metodën e pagesës dhe detajet
            </div>
        </div>
    </div>

    <!-- Connector Line -->
    <div class="connector-line" style="
        width: 80px; 
        height: 4px; 
        background: linear-gradient(to right, {{ $currentStep >= 3 ? '#1F6E38' : '#e9ecef' }} 0%, {{ $currentStep >= 3 ? '#1F6E38' : '#e9ecef' }} 100%); 
        margin: 0 0.5rem;
        border-radius: 2px;
        transition: all 0.5s ease;
        flex-shrink: 0;
    "></div>

    <!-- Step 3: Complete -->
    <div class="payment-step" style="display: flex; align-items: center; flex: 1; min-width: 200px;">
        <div class="step-circle" style="
            width: 45px; 
            height: 45px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold; 
            color: white;
            background-color: {{ $currentStep >= 3 ? '#1F6E38' : '#6c757d' }};
            {{ $currentStep == 3 ? 'box-shadow: 0 0 0 4px rgba(31, 110, 56, 0.3); transform: scale(1.1); animation: pulse 2s infinite;' : '' }}
            transition: all 0.3s ease;
        ">
            @if($currentStep >= 3)
                <svg style="width: 20px; height: 20px;" fill="white" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
            @else
                3
            @endif
        </div>
        <div class="step-content" style="margin-left: 1rem; text-align: left;">
            <div class="step-title" style="font-weight: 600; color: {{ $currentStep >= 3 ? '#1F6E38' : '#6c757d' }}; font-size: 1rem; margin-bottom: 0.25rem;">
                Përfundo
            </div>
            <div class="step-description" style="font-size: 0.8rem; color: #6c757d; line-height: 1.3;">
                Konfirmimi i pagesës dhe fatura
            </div>
        </div>
    </div>
</div>

<style>
    /* Enhanced animations */
    @keyframes pulse {
        0% { 
            box-shadow: 0 0 0 0 rgba(31, 110, 56, 0.4); 
        }
        50% { 
            box-shadow: 0 0 0 8px rgba(31, 110, 56, 0.1); 
        }
        100% { 
            box-shadow: 0 0 0 0 rgba(31, 110, 56, 0); 
        }
    }
    
    @keyframes stepComplete {
        0% { 
            transform: scale(1); 
        }
        50% { 
            transform: scale(1.2); 
        }
        100% { 
            transform: scale(1.1); 
        }
    }
    
    /* Improved mobile responsiveness */
    @media (max-width: 768px) {
        .payment-steps-container {
            flex-direction: column !important;
            gap: 1.5rem !important;
            padding: 1rem !important;
        }
        
        .payment-step {
            min-width: 100% !important;
            justify-content: center;
        }
        
        .connector-line {
            width: 4px !important;
            height: 40px !important;
            margin: 0 !important;
        }
        
        .step-content {
            text-align: center !important;
            margin-left: 1rem !important;
        }
        
        .step-title {
            font-size: 1.1rem !important;
        }
        
        .step-description {
            font-size: 0.9rem !important;
        }
    }
    
    /* Tablet view */
    @media (max-width: 1024px) and (min-width: 769px) {
        .payment-steps-container {
            padding: 1.25rem !important;
        }
        
        .payment-step {
            min-width: 180px !important;
        }
        
        .connector-line {
            width: 60px !important;
        }
    }
    
    /* Hover effects for better UX */
    .payment-step:hover .step-circle {
        transform: scale(1.05);
        transition: transform 0.2s ease;
    }
    
    /* Progress animation */
    .connector-line {
        background-size: 200% 100%;
        animation: progressFlow 2s ease-in-out;
    }
    
    @keyframes progressFlow {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style> 