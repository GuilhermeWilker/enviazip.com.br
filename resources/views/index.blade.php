<x-layouts::lp :title="$title ?? null">

    <section class="relative">
        <div class="boxed border-[var(--border)]">
            <div class="corner-dot left"></div>
            <div class="corner-dot right"></div>

            <div class="relative overflow-hidden">
                <div class="grid-bg absolute inset-0 h-[420px]" aria-hidden="true"></div>

                <div class="relative grid items-center gap-12 px-6 py-16 lg:grid-cols-[1.1fr_.9fr] lg:py-20">
                    <x-lp.hero />

                    <div id="enviar" class="scroll-mt-8 justify-self-center lg:justify-self-end w-full max-w-[460px]">
                        <x-upload-zip-file />
                    </div>
                </div>

                <h1>Trusted by</h1>

                <p>
                    google - G1 - Terra - UOL
                </p>
            </div>
        </div>
    </section>

</x-layouts::lp>
