@props(['name', 'placeholder'])

@php
    $wrapperID = $name . '-wrapper';
    $coordID = $name . '_coords';
    $enderecoID = $name . '_endereco';
@endphp


<div class="autocomplete__wrapper" id="{{ $wrapperID }}">
    <input type="hidden" name="{{ $coordID }}" id="{{ $coordID }}" value="{{ old($coordID) }}" data-coord-input>
    <input type="hidden" name="{{ $enderecoID }}" id="{{ $enderecoID }}" value="{{ old($enderecoID) }}">
    <input type="text" spellcheck="false" autocorrect="off" autocapitalize="off" autocomplete="off"
        placeholder="{{ $placeholder }}" value="{{ old($enderecoID) }}" required>
    <ul class="autocomplete__list"></ul>
</div>

<script>
    {
        const wrapper = document.getElementById("{{ $wrapperID }}");
        const list = wrapper.querySelector(".autocomplete__list");
        const coordsInput = document.getElementById("{{ $coordID }}");
        const enderecoInput = document.getElementById("{{ $enderecoID }}");
        const textInput = wrapper.querySelector("input[type=text]");

        let lastValidTextValue = textInput.value;
        let debounceTimer;

        function reset(resetInputs = true) {
            list.innerHTML = "";
            if (resetInputs) {
                textInput.value = "";
                coordsInput.value = "";
                enderecoInput.value = "";
            }
        }

        function handleUserInteractionEnd(event) {
            if (wrapper.contains(event.relatedTarget || event.target)) return;
            reset(lastValidTextValue !== textInput.value);
        }

        async function fetchResults(query) {
            if (query.length < 3) {
                list.innerHTML = "";
                return;
            }

            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&accept-language=pt-br&countrycodes=br&format=json`
                );
                const data = await res.json();
                if (data.length === 0) {
                    list.innerHTML = `
                <li class="autocomplete__empty">
                  Nenhum resultado encontrado
                </li>
                `
                } else {
                    list.innerHTML = data.map(item => `
                      <li class="autocomplete__item">
                        <button class="autocomplete-item__button" type="button" data-coords="${item.lon},${item.lat}" data-endereco="${item.display_name}">
                          ${item.display_name}
                        </button>
                      </li>
                    `).join("");
                }
            } catch (err) {
                console.error("Autocomplete fetch error:", err);
            }
        }

        textInput.addEventListener("input", (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchResults(e.target.value), 300);
        });

        list.addEventListener("click", (event) => {
            const button = event.target.closest(".autocomplete-item__button");
            if (!button) return;

            const coords = button.dataset.coords;
            const endereco = button.dataset.endereco;

            coordsInput.value = coords;
            enderecoInput.value = endereco;
            textInput.value = endereco;
            lastValidTextValue = endereco;
            wrapper.dispatchEvent(new Event("endereco-selected", {
                bubbles: true
            }));

            list.innerHTML = "";
        });

        document.addEventListener("click", handleUserInteractionEnd);
        wrapper.addEventListener("focusout", handleUserInteractionEnd);
    }
</script>
