import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../../services/auth.service';
import { AdminService } from '../../../services/admin.service';
import { Admin } from '../../../models/models';

@Component({
    selector: 'app-admin-dashboard',
    templateUrl: './admin-dashboard.component.html',
    styleUrls: ['./admin-dashboard.component.css']
})
export class AdminDashboardComponent implements OnInit {
    currentAdmin: Admin | null = null;
    stats = {
        products: 0,
        reviews: 0,
        admins: 0
    };

    constructor(
        private authService: AuthService,
        private adminService: AdminService
    ) { }

    ngOnInit(): void {
        this.authService.currentAdmin$.subscribe({
            next: (admin) => {
                this.currentAdmin = admin;
            }
        });

        this.loadStatistics();
    }

    loadStatistics(): void {
        // Загрузка статистики продуктов
        this.adminService.getProducts().subscribe({
            next: (products) => {
                this.stats.products = products.length;
            },
            error: (err) => console.error('Error loading products:', err)
        });

        // Загрузка статистики отзывов
        this.adminService.getReviews().subscribe({
            next: (reviews) => {
                this.stats.reviews = reviews.length;
            },
            error: (err) => console.error('Error loading reviews:', err)
        });

        // Загрузка статистики администраторов
        this.adminService.getAdministrators().subscribe({
            next: (admins) => {
                this.stats.admins = admins.length;
            },
            error: (err) => console.error('Error loading admins:', err)
        });
    }
}
