import { Component, ChangeDetectorRef, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink, RouterLinkActive, Router } from '@angular/router';
import { AuthService } from '../../core/auth/auth.service';

@Component({
  selector: 'app-main-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink, RouterLinkActive],
  templateUrl: './main-layout.component.html',
})
export class MainLayoutComponent implements OnInit {
  isMenuOpen = false;
  user: any = null;

  private cdr = inject(ChangeDetectorRef);
  private authService = inject(AuthService);

  ngOnInit() {
      this.user = this.authService.getUser();
  }

  toggleMenu() {
    this.isMenuOpen = !this.isMenuOpen;
    this.cdr.detectChanges();
  }

  logout() {
    this.authService.logout();
  }
}
