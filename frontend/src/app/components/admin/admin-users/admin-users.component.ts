import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AdminService } from '../../../services/admin.service';
import { Admin } from '../../../models/models';

@Component({
    selector: 'app-admin-users',
    templateUrl: './admin-users.component.html',
    styleUrls: ['./admin-users.component.css']
})
export class AdminUsersComponent implements OnInit {
    administrators: Admin[] = [];
    adminForm: FormGroup;
    loading = false;
    showForm = false;
    message = '';
    messageType = '';

    constructor(
        private fb: FormBuilder,
        private adminService: AdminService
    ) {
        this.adminForm = this.fb.group({
            username: ['', Validators.required],
            email: ['', [Validators.required, Validators.email]],
            password: ['', [Validators.required, Validators.minLength(6)]]
        });
    }

    ngOnInit(): void {
        this.loadAdministrators();
    }

    loadAdministrators(): void {
        this.loading = true;
        this.adminService.getAdministrators().subscribe({
            next: (data: Admin[]) => {
                this.administrators = data;
                this.loading = false;
            },
            error: (err: any) => {
                console.error(err);
                this.loading = false;
            }
        });
    }

    onAdd(): void {
        this.showForm = true;
        this.adminForm.reset();
    }

    onSubmit(): void {
        if (this.adminForm.valid) {
            const admin: Admin = this.adminForm.value;

            this.adminService.createAdministrator(admin).subscribe({
                next: () => {
                    this.showMessage('Администратор успешно создан', 'success');
                    this.loadAdministrators();
                    this.showForm = false;
                },
                error: (err: any) => {
                    this.showMessage('Ошибка при создании администратора. Возможно, username или email уже используется.', 'danger');
                    console.error(err);
                }
            });
        }
    }

    onDelete(id: number | undefined): void {
        if (id && confirm('Вы уверены, что хотите удалить этого администратора?')) {
            this.adminService.deleteAdministrator(id).subscribe({
                next: () => {
                    this.showMessage('Администратор удален', 'success');
                    this.loadAdministrators();
                },
                error: (err: any) => {
                    this.showMessage('Ошибка при удалении администратора', 'danger');
                    console.error(err);
                }
            });
        }
    }

    showMessage(text: string, type: string): void {
        this.message = text;
        this.messageType = type;
        setTimeout(() => {
            this.message = '';
        }, 3000);
    }
}
