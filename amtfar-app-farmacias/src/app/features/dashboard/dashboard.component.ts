import { Component, inject, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent implements OnInit {
  isLoading = true;
  empleados: any[] = [];

  private http = inject(HttpClient);
  private cdr = inject(ChangeDetectorRef);

  ngOnInit() {
    this.http.get<any>('http://127.0.0.1:8888/api/v1/empleados').subscribe({
      next: (res) => {
        if (res.status === 'success') {
          this.empleados = res.data;
        }
        setTimeout(() => {
          this.isLoading = false;
          this.cdr.detectChanges();
        }, 500); 
      },
      error: (err) => {
        console.error('Error cargando empleados:', err);
        this.isLoading = false;
        this.cdr.detectChanges();
      }
    });
  }
}
