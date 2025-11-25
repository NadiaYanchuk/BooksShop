import { Component } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth.service';

@Component({
    selector: 'app-login',
    templateUrl: './login.component.html',
    styleUrls: ['./login.component.css']
})
export class LoginComponent {
    loginForm: FormGroup;
    loading = false;
    error = '';
    returnUrl = '';

    constructor(
        private fb: FormBuilder,
        private authService: AuthService,
        private router: Router,
        private route: ActivatedRoute
    ) {
        this.loginForm = this.fb.group({
            username: ['', Validators.required],
            password: ['', Validators.required]
        });

        this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/admin/dashboard';
    }

    onSubmit(): void {
        if (this.loginForm.valid) {
            this.loading = true;
            this.error = '';

            const { username, password } = this.loginForm.value;

            this.authService.login(username, password).subscribe({
                next: (response: any) => {
                    if (response.admin) {
                        this.router.navigate([this.returnUrl]);
                    } else {
                        this.router.navigate(['/']);
                    }
                },
                error: (err: any) => {
                    this.error = 'Неверное имя пользователя или пароль';
                    this.loading = false;
                    console.error(err);
                }
            });
        }
    }
}
