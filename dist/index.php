<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="output.css" rel="stylesheet">
</head>

<body>
    <main>
        <section class="bg-gray-50">
            <div class="w-full max-w-7xl mx-auto">
                <header>
                    <nav class="flex flex-col xl:flex-row items-center justify-between p-4 lg:px-8 gap-y-4 xl:gap-x-4 overflow-x-hidden"
                        aria-label="Logo">
                        <a href="#" class="-m-1.5 p-1.5 flex-shrink-0 max-w-full">
                            <span class="sr-only">CICCO - Conacyt</span>
                            <img class="h-auto w-full object-contain"
                                style="max-height: 4rem; @media (min-width: 640px) { max-height: 5rem; }"
                                src="https://cicco.conacyt.gov.py/wp-content/uploads/2024/10/cicco-logos-gobernancia-todos-hor.png"
                                alt="CICCO - Conacyt">
                        </a>
                        <div
                            class="flex-1 flex flex-col xl:flex-row items-center xl:items-end justify-center xl:justify-end">
                            <div class="text-center xl:text-right truncate">
                                <h2
                                    class="text-sm sm:text-base lg:text-lg font-semibold leading-tight text-gray-900 truncate">
                                    Formulario de regístro de usuario</h2>
                                <p class="text-xs sm:text-sm font-light leading-tight text-gray-900 mt-1 truncate">
                                    Última
                                    actualización: <span class="">2024.10.16</span>
                                </p>
                            </div>
                        </div>
                    </nav>
                </header>
            </div>
        </section>
        <section class="max-w-3xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
            <?php include "components/form-front.php"; ?>
        </section>
    </main>
</body>

</html>
