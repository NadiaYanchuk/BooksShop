import { Component } from '@angular/core';
import { Router, NavigationEnd } from '@angular/router';
import { AuthService } from './services/auth.service';
import { filter } from 'rxjs/operators';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.css']
})
export class AppComponent {
    title = 'Интернет-магазин';
    showFooter = true;

    constructor(
        public authService: AuthService,
        private router: Router
    ) {
        this.router.events
            .pipe(filter(event => event instanceof NavigationEnd))
            .subscribe((event: any) => {
                this.showFooter = !event.url.includes('/login') &&
                    !event.url.includes('/register') &&
                    !event.url.includes('/admin');
            });
    }

    logout(): void {
        this.authService.logout().subscribe({
            next: () => {
                this.router.navigate(['/']);
            },
            error: (err) => console.error(err)
        });
    }
}
