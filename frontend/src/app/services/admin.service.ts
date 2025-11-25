import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Admin, Product, Review } from '../models/models';

@Injectable({
    providedIn: 'root'
})
export class AdminService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    // Администраторы
    getAdministrators(): Observable<Admin[]> {
        return this.http.get<Admin[]>(`${this.apiUrl}/admin/administrators.php`);
    }

    createAdministrator(admin: Admin): Observable<any> {
        return this.http.post(`${this.apiUrl}/admin/administrators.php`, admin);
    }

    deleteAdministrator(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/admin/administrators.php?id=${id}`);
    }

    // Продукты
    getProducts(): Observable<Product[]> {
        return this.http.get<Product[]>(`${this.apiUrl}/admin/products.php`);
    }

    // Отзывы
    getReviews(): Observable<Review[]> {
        return this.http.get<Review[]>(`${this.apiUrl}/admin/reviews.php`);
    }
}
