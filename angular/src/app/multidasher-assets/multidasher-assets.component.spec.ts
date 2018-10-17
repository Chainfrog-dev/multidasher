import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MultidasherAssetsComponent } from './multidasher-assets.component';

describe('MultidasherAssetsComponent', () => {
  let component: MultidasherAssetsComponent;
  let fixture: ComponentFixture<MultidasherAssetsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MultidasherAssetsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MultidasherAssetsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
