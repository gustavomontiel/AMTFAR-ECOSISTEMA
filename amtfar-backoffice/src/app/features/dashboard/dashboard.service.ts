import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class DashboardService {
  private apiUrl = 'http://amtfar-api.test/api/v1/admin/dashboard';

  constructor(private http: HttpClient) {}

  getKpis(): Observable<any> {
    return this.http.get<any>(this.apiUrl);
  }
}
