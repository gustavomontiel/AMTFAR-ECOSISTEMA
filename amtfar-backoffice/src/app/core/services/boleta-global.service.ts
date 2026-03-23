import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class BoletaGlobalService {
  private http = inject(HttpClient);
  private apiUrl = 'http://127.0.0.1:8888/api/v1/boletas';
  
  public boletas = signal<any[]>([]);

  getListadoGlobal(): Observable<any> {
    return this.http.get<any>(this.apiUrl).pipe(
      tap(res => {
        if (res.status === 'success') {
          this.boletas.set(res.data);
        }
      })
    );
  }

  descargarPdf(idBoleta: number): Observable<Blob> {
    return this.http.get(`${this.apiUrl}/${idBoleta}/pdf`, { responseType: 'blob' });
  }
}
