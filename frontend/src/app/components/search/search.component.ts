import { Component, OnInit } from '@angular/core';
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
        private productService: ProductService
    ) { }

    ngOnInit(): void {
        this.loadInitialBooks();
    }

    loadInitialBooks(): void {
        this.loading = true;

        // Загружаем все книги из нашей БД
        this.productService.getProducts().subscribe({
            next: (products) => {
                this.allBooks = products;
                this.filteredBooks = [...this.allBooks];

                // Получаем уникальные категории
                const categoriesSet = new Set(products.map(p => p.category));
                this.categories = Array.from(categoriesSet).sort();

                this.updatePagination();
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
        this.filteredBooks = this.allBooks.filter(book => {
            // Поиск по ключевому слову
            if (this.filters.keyword && this.filters.keyword.trim() !== '') {
                const keyword = this.filters.keyword.toLowerCase();
                const matchTitle = book.name.toLowerCase().includes(keyword);
                const matchAuthors = book.authors ? book.authors.toLowerCase().includes(keyword) : false;
                const matchDesc = book.description.toLowerCase().includes(keyword);
                if (!matchTitle && !matchAuthors && !matchDesc) return false;
            }

            // Фильтр по категории
            if (this.filters.category && this.filters.category !== '') {
                if (book.category !== this.filters.category) {
                    return false;
                }
            }

            // Фильтр по минимальной цене
            if (this.filters.minPrice !== null && this.filters.minPrice !== undefined) {
                if (book.price < this.filters.minPrice) return false;
            }

            // Фильтр по максимальной цене
            if (this.filters.maxPrice !== null && this.filters.maxPrice !== undefined) {
                if (book.price > this.filters.maxPrice) return false;
            }

            return true;
        });

        this.currentPage = 1; // Сбрасываем на первую страницу
        this.updatePagination();
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
        this.filteredBooks = [...this.allBooks];
        this.currentPage = 1;
        this.updatePagination();
    }

    toggleFilters(): void {
        this.filtersVisible = !this.filtersVisible;
    }
}
