import { Component, inject, OnInit, ChangeDetectorRef, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent implements OnInit {
  isLoading = signal(true);
  empleados = signal<any[]>([]);

  private http = inject(HttpClient);
  private cdr = inject(ChangeDetectorRef);

  ngOnInit() {
    this.http.get<any>('http://127.0.0.1:8888/api/v1/empleados').subscribe({
      next: (res) => {
        if (res.status === 'success') {
          this.empleados.set(res.data);
        }
        setTimeout(() => {
          this.isLoading.set(false);
        }, 500); 
      },
      error: (err) => {
        console.error('Error cargando empleados:', err);
        this.isLoading.set(false);
      }
    });
  }
}
