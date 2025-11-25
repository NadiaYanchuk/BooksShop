import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { GoogleBook } from '../models/models';

@Injectable({
    providedIn: 'root'
})
export class BooksService {
    private apiUrl = 'https://www.googleapis.com/books/v1/volumes';

    constructor(private http: HttpClient) { }

    // Популярные книги (бестселлеры)
    getPopularBooks(): Observable<GoogleBook[]> {
        const query = 'subject:Fiction&orderBy=relevance&maxResults=12';
        return this.http.get<any>(`${this.apiUrl}?q=${query}&langRestrict=ru`)
            .pipe(map(response => {
                return response.items || [];
            }));
    }

    // Новые книги (недавно опубликованные)
    getNewBooks(): Observable<GoogleBook[]> {
        const query = 'subject:Literature&orderBy=newest&maxResults=12';
        return this.http.get<any>(`${this.apiUrl}?q=${query}&langRestrict=ru`)
            .pipe(map(response => {
                return response.items || [];
            }));
    }

    // Рекомендации на основе категории (для зарегистрированных пользователей)
    getRecommendations(category: string = 'fiction'): Observable<GoogleBook[]> {
        const query = `subject:${category}&orderBy=relevance&maxResults=8`;
        return this.http.get<any>(`${this.apiUrl}?q=${query}&langRestrict=ru`)
            .pipe(map(response => response.items || []));
    }

    // Поиск книг по ключевому слову
    searchBooks(keyword: string, category?: string): Observable<GoogleBook[]> {
        let query = keyword;
        if (category) {
            query += `+subject:${category}`;
        }
        return this.http.get<any>(`${this.apiUrl}?q=${query}&maxResults=20&langRestrict=ru`)
            .pipe(map(response => response.items || []));
    }

    // Получить детали книги
    getBookDetails(bookId: string): Observable<GoogleBook> {
        return this.http.get<GoogleBook>(`${this.apiUrl}/${bookId}`);
    }

    // Книги по категории
    getBooksByCategory(category: string, maxResults: number = 12): Observable<GoogleBook[]> {
        const query = `subject:${category}&orderBy=relevance&maxResults=${maxResults}`;
        return this.http.get<any>(`${this.apiUrl}?q=${query}&langRestrict=ru`)
            .pipe(map(response => response.items || []));
    }
}
