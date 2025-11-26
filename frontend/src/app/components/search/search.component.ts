import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { ProductService } from '../../services/product.service';
import { Product } from '../../models/models';

@Component({
    selector: 'app-search',
    templateUrl: './search.component.html',
    styleUrls: ['./search.component.css']
})
export class SearchComponent implements OnInit {
    allBooks: Product[] = [];
    filteredBooks: Product[] = [];
    paginatedBooks: Product[] = [];
    categories: string[] = [];
    loading = false;
    filtersVisible = false;

    // Фильтры
    filters = {
        keyword: '',
        category: '',
        minPrice: null as number | null,
        maxPrice: null as number | null
    };

    // Пагинация
    currentPage = 1;
    itemsPerPage = 12;
    totalPages = 1;

    constructor(
        private productService: ProductService,
        private route: ActivatedRoute
    ) { }

    ngOnInit(): void {
        this.route.queryParams.subscribe(params => {
            if (params['search']) {
                this.filters.keyword = params['search'];
            }
            this.loadInitialBooks();
        });
    }

    loadInitialBooks(): void {
        this.loading = true;

        this.productService.getProducts().subscribe({
            next: (products) => {
                this.allBooks = products;

                const categoriesSet = new Set(products.map(p => p.category));
                this.categories = Array.from(categoriesSet).sort();

                if (this.filters.keyword) {
                    this.applyFilters();
                } else {
                    this.filteredBooks = [...this.allBooks];
                    this.updatePagination();
                }

                this.loading = false;
            },
            error: (error) => {
                console.error('Ошибка загрузки книг:', error);
                this.loading = false;
                alert('Не удалось загрузить книги. Проверьте соединение с сервером.');
            }
        });
    }

    applyFilters(): void {
        this.loading = true;

        this.productService.getProductsWithFilters(this.filters).subscribe({
            next: (products) => {
                this.filteredBooks = products;
                this.currentPage = 1; // Сбрасываем на первую страницу
                this.updatePagination();
                this.loading = false;
            },
            error: (error) => {
                console.error('Ошибка фильтрации:', error);
                this.loading = false;
                alert('Не удалось применить фильтры. Проверьте соединение с сервером.');
            }
        });
    }

    updatePagination(): void {
        this.totalPages = Math.ceil(this.filteredBooks.length / this.itemsPerPage);
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        this.paginatedBooks = this.filteredBooks.slice(startIndex, endIndex);
    }

    goToPage(page: number): void {
        if (page >= 1 && page <= this.totalPages) {
            this.currentPage = page;
            this.updatePagination();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    get pages(): number[] {
        const pages = [];
        const maxVisible = 5;
        let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
        let end = Math.min(this.totalPages, start + maxVisible - 1);

        if (end - start < maxVisible - 1) {
            start = Math.max(1, end - maxVisible + 1);
        }

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
        return pages;
    }

    resetFilters(): void {
        this.filters = {
            keyword: '',
            category: '',
            minPrice: null,
            maxPrice: null
        };
        this.currentPage = 1;
        this.applyFilters();
    }

    toggleFilters(): void {
        this.filtersVisible = !this.filtersVisible;
    }

    onImageError(event: Event): void {
        const img = event.target as HTMLImageElement;
        img.src = 'assets/no-image.png';
    }
}
