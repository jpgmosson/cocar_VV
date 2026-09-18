@props(['name', 'mask', 'id' => null, 'value' => '', 'placeholder' => null])

@php
    $id = $id ?? $name;
    $displayId = $id . '_display';

    $config = match ($mask) {
        'cpf' => [
            'placeholder' => '000.000.000-00',
            'max_length_value' => 11,
            'max_length_display' => 14,
            'format' =>
                'value = value.replace(/(\d{3})(\d)/, "$1.$2").replace(/(\d{3})(\d)/, "$1.$2").replace(/(\d{3})(\d{1,2})$/, "$1-$2");',
        ],
        'cnpj' => [
            'placeholder' => '00.000.000/0000-00',
            'max_length_value' => 14,
            'max_length_display' => 18,
            'format' =>
                'value = value.replace(/(\d{2})(\d)/, "$1.$2").replace(/(\d{3})(\d)/, "$1.$2").replace(/(\d{3})(\d)/, "$1/$2").replace(/(\d{4})(\d{1,2})/, "$1-$2");',
        ],
        'cnh' => [
            'placeholder' => '00000000000',
            'max_length_value' => 11,
            'max_length_display' => 11,
            'format' => '',
        ],
    };

    $placeholder = $placeholder ?? $config['placeholder'];
@endphp

<input type="text" id="{{ $displayId }}" placeholder="{{ $placeholder }}"
    maxlength="{{ $config['max_length_display'] }}" {{ $attributes->except('id') }} />
<input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ preg_replace('/\D/', '', $value) }}"
    @if ($attributes->has('disabled')) disabled @endif />

<script>
    (function() {
        const displayInput = document.getElementById("{{ $displayId }}");
        const hiddenInput = document.getElementById("{{ $id }}");

        function formatInputValue(val) {
            let value = val.replace(/\D/g, "").substring(0, {{ $config['max_length_value'] }});
            hiddenInput.value = value;

            if (!value) {
                displayInput.value = "";
                return;
            }

            {!! $config['format'] !!}

            displayInput.value = value;
        }

        displayInput.addEventListener("input", (event) => {
            formatInputValue(event.target.value);
        });

        @if ($value)
            formatInputValue("{{ $value }}");
        @endif
    })();
</script>
