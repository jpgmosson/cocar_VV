@if ($pedido)
    <a href="{{ route('pedido-carona.show', ['pedidoCarona' => $pedido->id]) }}"
        class="floating-pill floating-pill--orange">
        <div class="floating-pill__icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m.275-3q.425 0 .713-.288T13 16v-3.3l2.8-2.8q.275-.275.275-.7t-.275-.7t-.7-.275t-.7.275L11.3 11.6q-.15.15-.225.338T11 12.35v3.65q0 .425.288.713t.712.287M12 12" />
            </svg>

        </div>
        <div class="floating-pill__content">
            <span class="floating-pill__label">Pedido</span>
            <span class="floating-pill__value">{{ $pedido->status()->label() }}</span>
        </div>
    </a>
@elseif($trajeto)
    <a href="{{ route('trajeto.show', ['trajeto' => $trajeto->id]) }}" class="floating-pill">
        <div class="floating-pill__icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5s2.5 1.12 2.5 2.5s-1.12 2.5-2.5 2.5z" />
            </svg>
        </div>
        <div class="floating-pill__content">
            <span class="floating-pill__label">Trajeto</span>
            <span class="floating-pill__value">{{ $trajeto->status->label() }}</span>
        </div>
    </a>
@endif

<script>
    const pill = document.querySelector('.floating-pill');

    let isDragging = false;
    let hasDragged = false;
    let startX, startY, startLeft, startTop;

    pill.addEventListener('dragstart', (e) => e.preventDefault());

    pill.addEventListener('mousedown', (e) => {
        isDragging = true;
        hasDragged = false;

        const rect = pill.getBoundingClientRect();
        startLeft = rect.left;
        startTop = rect.top;
        startX = e.clientX;
        startY = e.clientY;

        pill.style.right = 'auto';
        pill.style.bottom = 'auto';
        pill.style.left = `${startLeft}px`;
        pill.style.top = `${startTop}px`;

        pill.classList.add('floating-pill--dragging');
    });

    document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        hasDragged = true;

        const dx = e.clientX - startX;
        const dy = e.clientY - startY;

        pill.style.left = `${startLeft + dx}px`;
        pill.style.top = `${startTop + dy}px`;
    });

    document.addEventListener('mouseup', () => {
        if (!isDragging) return;
        isDragging = false;
        pill.classList.remove('floating-pill--dragging');
    });

    pill.addEventListener('click', (e) => {
        if (hasDragged) e.preventDefault();
    });
</script>
