export default class CatalogMobileSlider {
    catalogWrapper = document.querySelector('.catalog-wrapper');
    viewportwidth = document.documentElement.clientWidth;
    coordinateX = 0;

    constructor () {
        document.addEventListener("DOMContentLoaded", () => {

            const isMobile = this.viewportwidth < 992;

            if (isMobile && this.catalogWrapper) {
                const parentLinks = this.catalogWrapper.querySelectorAll('.categories-parent-list-item > a');
        
                parentLinks.forEach((link) => {
                    link.addEventListener('click', (e) => {
                        const thisLink = e.target;
                        const catId = thisLink.dataset.productCat;

                        if (catId) {
                            e.preventDefault();
                            this.ajaxLoadCategories(catId, (res) => {
                                this.renderCategories('.catalog-children-slide', res);
                                this.moveSlider('forward');
                            });
                        }
                    })
                });
            }
        });
    }

    ajaxLoadCategories (id, callback) {
        const data = new FormData();
        data.append('action', 'get_children_cats');
        data.append('cat_id', id);

        fetch(dataObj.ajaxurl, {
            method: 'POST',
            body: data
        })
        .then(response => response.text())
        .then(callback)
        .catch(err => console.log(err));
    }

    renderCategories (slideSelector, html) {
        const slide = this.catalogWrapper.querySelector(slideSelector);
        slide.innerHTML = html;

        const childLinks = slide.querySelectorAll('.catalog-slide-item > a');
        const backLink = slide.querySelector('.catalog-slide-backlink');

        childLinks.forEach((link) => {
            link.addEventListener('click', (e) => {
                const thisLink = e.target;
                const catId = thisLink.dataset.productCat;

                if (catId) {
                    e.preventDefault();
                    this.ajaxLoadCategories(catId, (res) => {
                        this.renderCategories('.catalog-grandchildren-slide', res);
                        this.moveSlider('forward');
                    });
                }
            })
        });

        backLink.addEventListener('click', (e) => {
            e.preventDefault();
            this.moveSlider('backward');
        });
    }

    moveSlider (direction) {
        if (direction === 'backward') {
            this.coordinateX += this.viewportwidth;
        } else {
            this.coordinateX -= this.viewportwidth;
        }

        this.catalogWrapper.style.transform = `translateX(${this.coordinateX}px)`;
    }
}