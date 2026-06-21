function initFilters(selector, table, filters){

    const botones =
        document.querySelectorAll(selector);

    botones.forEach(btn => {

        btn.addEventListener("click", () => {

            botones.forEach(b =>
                b.classList.remove(
                    "filter-chip--active"
                )
            );

            btn.classList.add(
                "filter-chip--active"
            );

            const filtro =
                btn.dataset.filter;

            table.search(
                filters[filtro] || ""
            ).draw();

        });

    });

}