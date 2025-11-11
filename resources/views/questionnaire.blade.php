@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="text-center fw-bold text-primary mb-4">Patient Questionnaire</h2>

    @if(session('questionnaire_instance_id'))
        <div class="alert alert-secondary text-center mb-4">
            <strong>Questionnaire Instance ID:</strong> {{ session('questionnaire_instance_id') }}
        </div>
    @endif

    @if(!empty($questions))
        <div class="card shadow-sm border-0 mt-4 mx-auto" style="max-width: 600px;">
            <div class="card-body p-4">

                {{-- Progress bar --}}
                <div class="progress mb-4" style="height: 10px;">
                    <div id="progressBar" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                </div>

                <form id="questionnaireForm" method="POST" action="{{ url('/submit-questionnaire') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product['id'] ?? '' }}">

                    @foreach($questions as $index => $q)
                        @php
                            $label = $q['data']['label'] ?? 'Untitled Question';
                            $options = $q['data']['props']['options'] ?? [];
                            $type = $q['data']['type'] ?? 'text';
                        @endphp

                        <div class="question-step" data-step="{{ $index }}" style="display: none;">
                            <h5 class="fw-semibold mb-3">{{ $label }}</h5>

                            @if($type === 'select' && !empty($options))
                                <select name="answers[{{ $q['id'] ?? $index }}]" class="form-select" required>
                                    <option value="">Select an option</option>
                                    @foreach($options as $option)
                                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text"
                                       name="answers[{{ $q['id'] ?? $index }}]"
                                       class="form-control"
                                       placeholder="Your answer"
                                       required>
                            @endif
                        </div>
                    @endforeach

                    {{-- Navigation buttons --}}
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" id="prevBtn" class="btn btn-outline-secondary px-4" disabled>Back</button>
                        <button type="button" id="nextBtn" class="btn btn-primary px-4">Next</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="alert alert-info text-center mt-4">
            No questions available for this product.
        </div>
    @endif

    {{-- Debug (optional) --}}
    @if(isset($rawResponse))
        <div class="mt-5">
            <h5 class="fw-bold text-center mb-3">🧩 Debug: Raw Telegra API Response</h5>
            <pre style="background:#f8f9fa; padding:15px; border-radius:10px; max-height:400px; overflow:auto; font-size:13px;">
{{ json_encode($rawResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
            </pre>
        </div>
    @endif
</div>

{{-- Styling + JS --}}
<style>
    .btn-primary {
        background: linear-gradient(90deg, #007bff, #0056b3);
        border: none;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    }
    .question-step {
        animation: fadeIn 0.4s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const steps = document.querySelectorAll('.question-step');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const progressBar = document.getElementById('progressBar');
    let currentStep = 0;

    function showStep(index) {
        steps.forEach((step, i) => step.style.display = (i === index) ? 'block' : 'none');
        prevBtn.disabled = index === 0;
        nextBtn.textContent = index === steps.length - 1 ? 'Submit' : 'Next';
        progressBar.style.width = `${((index + 1) / steps.length) * 100}%`;
    }

    nextBtn.addEventListener('click', function() {
        const currentQuestion = steps[currentStep];
        const input = currentQuestion.querySelector('input, select');
        if (input && !input.value) {
            input.classList.add('is-invalid');
            return;
        } else {
            input?.classList.remove('is-invalid');
        }

        if (currentStep < steps.length - 1) {
            currentStep++;
            showStep(currentStep);
        } else {
            document.getElementById('questionnaireForm').submit();
        }
    });

    prevBtn.addEventListener('click', function() {
        if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
        }
    });

    // Initialize first question
    showStep(0);
});
</script>
@endsection
