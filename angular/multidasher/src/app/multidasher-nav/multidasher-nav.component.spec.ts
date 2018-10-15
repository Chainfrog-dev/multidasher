
import { fakeAsync, ComponentFixture, TestBed } from '@angular/core/testing';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MultidasherNavComponent } from './multidasher-nav.component';

describe('MultidasherNavComponent', () => {
  let component: MultidasherNavComponent;
  let fixture: ComponentFixture<MultidasherNavComponent>;

  beforeEach(fakeAsync(() => {
    TestBed.configureTestingModule({
      imports: [MatSidenavModule],
      declarations: [MultidasherNavComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MultidasherNavComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should compile', () => {
    expect(component).toBeTruthy();
  });
});
