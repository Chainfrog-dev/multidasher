import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MultidasherInfoComponent } from './multidasher-info.component';

describe('MultidasherInfoComponent', () => {
  let component: MultidasherInfoComponent;
  let fixture: ComponentFixture<MultidasherInfoComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MultidasherInfoComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MultidasherInfoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
