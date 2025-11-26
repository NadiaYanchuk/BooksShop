import { Component, OnInit } from '@angular/core';
import { ProductService } from '../../services/product.service';
import { AuthService } from '../../services/auth.service';
import { ReviewService } from '../../services/review.service';
import { Product, Review } from '../../models/models';

@Component({
    selector: 'app-home',
    templateUrl: './home.component.html',
    styleUrls: ['./home.component.css']
})
export class HomeComponent implements OnInit {
    popularBooks: Product[] = [];
    newBooks: Product[] = [];
    recommendedBooks: Product[] = [];
    popularLoading = true;
    newLoading = true;
    recommendedLoading = true;
    reviews: Review[] = [];
    reviewsLoading = true;

    constructor(
        private productService: ProductService,
        public authService: AuthService,
        private reviewService: ReviewService
    ) { }

    ngOnInit(): void {
        this.loadPopularBooks();
        this.loadNewBooks();
        this.loadReviews();

        // Рекомендации только для авторизованных пользователей
        if (this.authService.isAuthenticated()) {
            this.loadRecommendedBooks();
        } else {
            this.recommendedLoading = false;
        }
    }
    loadReviews(): void {
        this.reviewsLoading = true;
        this.reviewService.getApprovedReviews().subscribe({
            next: (reviews) => {
                this.reviews = reviews.slice(0, 6); // Только 6 последних
                this.reviewsLoading = false;
            },
            error: (error) => {
                console.error('Ошибка загрузки отзывов:', error);
                this.reviewsLoading = false;
            }
        });
    }

    loadRecommendedBooks(): void {
        this.recommendedLoading = true;
        this.productService.getProducts().subscribe({
            next: (products) => {
                // Берём случайные 3 книги
                const shuffled = [...products].sort(() => 0.5 - Math.random());
                this.recommendedBooks = shuffled.slice(0, 3);
                this.recommendedLoading = false;
            },
            error: (err) => {
                console.error('Ошибка загрузки рекомендаций:', err);
                this.recommendedLoading = false;
            }
        });
    }

    loadPopularBooks(): void {
        this.popularLoading = true;
        this.productService.getProducts().subscribe({
            next: (products) => {
                // Берём первые 12 книг как популярные
                this.popularBooks = products.slice(0, 12);
                this.popularLoading = false;
            },
            error: (err) => {
                console.error('Ошибка загрузки популярных книг:', err);
                this.popularLoading = false;
            }
        });
    }

    loadNewBooks(): void {
        this.newLoading = true;
        this.productService.getProducts().subscribe({
            next: (products) => {
                // Берём последние 12 книг как новинки (сортировка по дате создания)
                const sorted = [...products].sort((a, b) => {
                    return new Date(b.created_at || 0).getTime() - new Date(a.created_at || 0).getTime();
                });
                this.newBooks = sorted.slice(0, 12);
                this.newLoading = false;
            },
            error: (err) => {
                console.error('Ошибка загрузки новых книг:', err);
                this.newLoading = false;
            }
        });
    }

    onImageError(event: Event): void {
        const img = event.target as HTMLImageElement;
        img.src = 'assets/no-image.png';
    }
}
