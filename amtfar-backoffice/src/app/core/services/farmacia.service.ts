import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class FarmaciaService {
  private http = inject(HttpClient);
  private apiUrl = 'http://127.0.0.1:8888/api/v1/farmacias';
  
  public farmacias = signal<any[]>([]);

  getListado(): Observable<any> {
    return this.http.get<any>(this.apiUrl).pipe(
      tap(res => {
        if (res.status === 'success') {
          this.farmacias.set(res.data);
        }
      })
    );
  }

  crear(data: any): Observable<any> {
    return this.http.post<any>(this.apiUrl, data);
  }

  actualizar(id: number, data: any): Observable<any> {
    return this.http.put<any>(`${this.apiUrl}/${id}`, data);
  }

  bajaLaboral(id: number): Observable<any> {
    return this.http.put<any>(`${this.apiUrl}/${id}/baja`, {});
  }
}
