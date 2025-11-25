import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable({
    providedIn: 'root'
})
export class AuthGuard implements CanActivate {
    constructor(
        private router: Router,
        private authService: AuthService
    ) { }

    canActivate(
        route: ActivatedRouteSnapshot,
        state: RouterStateSnapshot
    ): boolean {
        // Проверка что пользователь авторизован И является админом
        if (this.authService.isAuthenticated() && this.authService.isAdmin()) {
            return true;
        }

        // Если пользователь не админ, перенаправление на главную
        if (this.authService.isAuthenticated() && !this.authService.isAdmin()) {
            this.router.navigate(['/']);
            return false;
        }

        // Если не авторизован, перенаправление на страницу входа
        this.router.navigate(['/login'], { queryParams: { returnUrl: state.url } });
        return false;
    }
}
