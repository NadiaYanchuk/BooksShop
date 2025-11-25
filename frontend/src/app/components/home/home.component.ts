import { Component, OnInit } from '@angular/core';
import { BooksService } from '../../services/books.service';
import { AuthService } from '../../services/auth.service';
import { ReviewService } from '../../services/review.service';
import { GoogleBook, BookSection, Review } from '../../models/models';

@Component({
    selector: 'app-home',
    templateUrl: './home.component.html',
    styleUrls: ['./home.component.css']
})
export class HomeComponent implements OnInit {
    popularBooks: BookSection = { title: 'Популярные книги', books: [], loading: true };
    newBooks: BookSection = { title: 'Новинки', books: [], loading: true };
    reviews: Review[] = [];
    reviewsLoading = true;

    constructor(
        private booksService: BooksService,
        public authService: AuthService,
        private reviewService: ReviewService
    ) { }

    ngOnInit(): void {
        // Загружаем публичный контент для всех пользователей
        this.loadPopularBooks();
        this.loadNewBooks();
        this.loadReviews();
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

    loadPopularBooks(): void {
        this.popularBooks.loading = true;
        this.booksService.getPopularBooks().subscribe({
            next: (books) => {
                this.popularBooks.books = books;
                this.popularBooks.loading = false;
            },
            error: (err) => {
                console.error('Ошибка загрузки популярных книг:', err);
                this.popularBooks.loading = false;
            }
        });
    }

    loadNewBooks(): void {
        this.newBooks.loading = true;
        this.booksService.getNewBooks().subscribe({
            next: (books) => {
                this.newBooks.books = books;
                this.newBooks.loading = false;
            },
            error: (err) => {
                console.error('Ошибка загрузки новых книг:', err);
                this.newBooks.loading = false;
            }
        });
    }

    getBookImage(book: GoogleBook): string {
        return book.volumeInfo.imageLinks?.thumbnail ||
            book.volumeInfo.imageLinks?.smallThumbnail ||
            'https://via.placeholder.com/128x196?text=No+Image';
    }

    getBookAuthors(book: GoogleBook): string {
        return book.volumeInfo.authors?.join(', ') || 'Автор неизвестен';
    }

    getBookPrice(book: GoogleBook): number {
        return 10;
    }
}
