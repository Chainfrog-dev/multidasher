
import { fakeAsync, ComponentFixture, TestBed } from '@angular/core/testing';

import { MultidasherDashboardComponent } from './multidasher-dashboard.component';

describe('MultidasherDashboardComponent', () => {
  let component: MultidasherDashboardComponent;
  let fixture: ComponentFixture<MultidasherDashboardComponent>;

  beforeEach(fakeAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ MultidasherDashboardComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MultidasherDashboardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should compile', () => {
    expect(component).toBeTruthy();
  });
});
